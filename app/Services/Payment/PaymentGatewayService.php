<?php

namespace App\Services\Payment;

use App\Adapters\Contracts\PaymentAdapterInterface;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use App\Models\Reservation;
use App\Services\Audit\AuditLogger;
use App\Services\Fo\FolioService;
use App\Services\Integrations\AdapterFactory;
use App\Services\Integrations\ProviderRegistry;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Gateway payment orchestration with a strict payment state machine:
 *
 *   pending → paid
 *   pending → failed
 *   paid    → refunded (via refund flow)
 *
 * Callbacks are ALWAYS signature-verified upstream (controller enforces 403 on
 * failure). This service additionally enforces idempotency (duplicate
 * callbacks are no-ops), state transition validation (a paid transaction can
 * never be demoted back to pending/failed), and amount matching when the
 * provider reports the gross amount.
 */
class PaymentGatewayService
{
    public function __construct(
        protected ProviderRegistry $registry,
        protected AdapterFactory $factory,
        protected FolioService $folioService,
        protected NotificationDispatcher $dispatcher,
        protected AuditLogger $audit,
    ) {}

    /**
     * Supported payment methods for the active provider of a property.
     * Drives the booking engine UI — only methods the provider can actually
     * process are offered to the guest.
     *
     * @return array<int, array{id: string, label: string, group: string}>
     */
    public function availablePaymentMethods(int $propertyId): array
    {
        $provider = $this->resolveProvider($propertyId);
        if (! $provider) {
            return [];
        }

        // Provider-specific override via extra_config.supported_methods
        // (written by the payment settings presets) with a legacy fallback.
        $configured = $provider->extra_config['supported_methods']
            ?? $provider->extra_config['payment_methods']
            ?? null;
        if (is_array($configured) && $configured !== []) {
            $known = self::KNOWN_METHODS();
            $methods = [];
            foreach ($configured as $id) {
                if (isset($known[$id])) {
                    $methods[] = $known[$id];
                }
            }
            if ($methods !== []) {
                return $methods;
            }
        }

        // Default Midtrans-style capability set per integration format.
        return match ($provider->api_format) {
            'qris_flow' => [self::KNOWN_METHODS()['qris']],
            'direct_charge' => [self::KNOWN_METHODS()['credit_card']],
            default => [
                self::KNOWN_METHODS()['qris'],
                self::KNOWN_METHODS()['virtual_account'],
                self::KNOWN_METHODS()['credit_card'],
                self::KNOWN_METHODS()['ewallet'],
                self::KNOWN_METHODS()['convenience_store'],
            ],
        };
    }

    /** Canonical catalog of payment methods (Midtrans-compatible identifiers). */
    public static function KNOWN_METHODS(): array
    {
        return [
            'qris' => ['id' => 'qris', 'label' => 'QRIS', 'group' => 'QR'],
            'virtual_account' => ['id' => 'virtual_account', 'label' => 'Virtual Account (Transfer Bank)', 'group' => 'Bank Transfer'],
            'bank_transfer' => ['id' => 'bank_transfer', 'label' => 'Transfer Bank Manual', 'group' => 'Bank Transfer'],
            'credit_card' => ['id' => 'credit_card', 'label' => 'Kartu Kredit / Debit', 'group' => 'Kartu'],
            'ewallet' => ['id' => 'ewallet', 'label' => 'E-Wallet', 'group' => 'E-Wallet'],
            'gopay' => ['id' => 'gopay', 'label' => 'GoPay', 'group' => 'E-Wallet'],
            'shopee_pay' => ['id' => 'shopee_pay', 'label' => 'ShopeePay', 'group' => 'E-Wallet'],
            'convenience_store' => ['id' => 'convenience_store', 'label' => 'Gerai Retail / Convenience Store', 'group' => 'Gerai Retail'],
            'retail_outlet' => ['id' => 'retail_outlet', 'label' => 'Retail Outlet', 'group' => 'Gerai Retail'],
        ];
    }

    /**
     * Buat transaksi pembayaran untuk reservasi.
     * The FolioPayment is created with status=pending; the folio balance is
     * only reduced once the callback confirms settlement.
     *
     * @return array{ok: bool, redirect_url: ?string, transaction_id: ?string, payment_method: string, raw: array}
     */
    public function createTransaction(Reservation $reservation, string $method, array $params = []): array
    {
        $folios = $reservation->folios;
        if ($folios->isEmpty()) {
            return ['ok' => false, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Folio tidak ditemukan'];
        }

        $folio = $folios->first();
        $folio->recalculate();
        $amount = (int) round((float) $folio->balance);

        if ($amount <= 0) {
            return ['ok' => true, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Saldo sudah lunas'];
        }

        // Double-submit guard: if this folio already has an unconfirmed
        // pending transaction, reuse it instead of creating a second one.
        // (Two pending txns both settling would double-pay the folio.)
        $existingPending = $folio->payments()
            ->where('status', FolioPayment::STATUS_PENDING)
            ->where('is_void', false)
            ->first();
        if ($existingPending) {
            return [
                'ok' => true,
                'redirect_url' => $existingPending->gateway_payload['redirect_url'] ?? null,
                'transaction_id' => $existingPending->reference_no,
                'payment_method' => $existingPending->method,
                'raw' => ['reused' => true],
            ];
        }

        // Only offer methods the active provider supports.
        $allowed = collect($this->availablePaymentMethods($reservation->property_id))->pluck('id')->all();
        if ($allowed !== [] && ! in_array($method, $allowed, true)) {
            return ['ok' => false, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Metode pembayaran tidak didukung oleh penyedia pembayaran aktif.'];
        }

        $adapter = $this->resolveAdapter($reservation->property_id);
        $provider = $this->resolveProvider($reservation->property_id);

        if (! $adapter) {
            return ['ok' => false, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Belum ada payment gateway yang dikonfigurasi. Silakan tambahkan provider di Pengaturan → Payment Gateway.'];
        }

        $transactionId = 'PAY-'.$reservation->ref.'-'.now()->format('His');

        $payload = array_merge([
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $reservation->primaryGuest?->first_name ?? 'Guest',
                'last_name' => $reservation->primaryGuest?->last_name ?? '',
                'email' => $reservation->primaryGuest?->email ?? '',
                'phone' => $reservation->primaryGuest?->phone ?? '',
            ],
            'item_details' => [[
                'id' => $reservation->ref,
                'price' => $amount,
                'quantity' => 1,
                'name' => 'Reservasi Hotel - '.$reservation->ref,
            ]],
            'payment_method' => $method,
            'callback_url' => route('booking.payment-callback', $reservation->ref),
            'return_url' => route('booking.payment-return', $reservation->ref),
        ], $params);

        try {
            $result = $adapter->charge($payload);

            if ($result['ok']) {
                $folio->payments()->create([
                    'property_id' => $reservation->property_id,
                    'payment_date' => now()->toDateString(),
                    'amount' => $amount,
                    'method' => $method,
                    'status' => FolioPayment::STATUS_PENDING,
                    'provider_id' => $provider?->id,
                    'reference_no' => $result['transaction_id'] ?? $transactionId,
                    'gateway_payload' => [
                        'txn_id' => $result['transaction_id'] ?? $transactionId,
                        'method' => $method,
                        'provider' => $provider?->name,
                        'status' => FolioPayment::STATUS_PENDING,
                        'redirect_url' => $result['redirect_url'] ?? null,
                    ],
                    'cashier_id' => null,
                ]);
                $folio->recalculate();
                $reservation->payment_status = 'pending';
                $reservation->save();
            }

            return [
                'ok' => $result['ok'],
                'redirect_url' => $result['redirect_url'] ?? null,
                'transaction_id' => $result['transaction_id'] ?? $transactionId,
                'payment_method' => $method,
                'raw' => $result['raw'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error("PaymentGatewayService::createTransaction gagal: {$e->getMessage()}", [
                'reservation_id' => $reservation->id,
                'method' => $method,
            ]);

            return ['ok' => false, 'redirect_url' => null, 'transaction_id' => $transactionId, 'payment_method' => $method, 'raw' => [], 'error' => 'Gagal membuat transaksi pembayaran. Silakan coba lagi.'];
        }
    }

    public function verifyCallback(string $reservationRef, array $payload, array $headers = []): bool
    {
        $reservation = Reservation::where('ref', $reservationRef)->first();
        if (! $reservation) {
            return false;
        }

        $adapter = $this->resolveAdapter($reservation->property_id);
        if (! $adapter) {
            return false;
        }

        return $adapter->verifyCallback($payload, $headers);
    }

    /**
     * Process a verified callback. Idempotent + state-transition safe:
     * - unknown ref/status → logged, ignored
     * - already settled → no-op (late/duplicate callback cannot demote)
     * - amount mismatch → rejected, security-logged
     */
    public function handleCallback(string $reservationRef, string $status, array $payload = []): void
    {
        DB::transaction(function () use ($reservationRef, $status, $payload) {
            $reservation = Reservation::where('ref', $reservationRef)->with('folios')->first();
            if (! $reservation) {
                Log::warning('Payment callback for unknown reservation', ['ref' => $reservationRef]);

                return;
            }
            $folio = $reservation->folios->first();
            if (! $folio) {
                return;
            }

            $transactionId = $payload['order_id'] ?? $payload['transaction_id'] ?? null;
            $payment = $folio->payments()
                ->where('reference_no', $transactionId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('Payment callback for unknown transaction', [
                    'reservation_ref' => $reservationRef,
                    'reference_no' => $transactionId,
                ]);

                return;
            }

            // Amount match when provider reports gross_amount.
            if (isset($payload['gross_amount'])) {
                $reported = (float) $payload['gross_amount'];
                if (abs($reported - (float) $payment->amount) > 0.01) {
                    Log::channel('audit')->error('Payment callback amount mismatch — rejected', [
                        'reference_no' => $transactionId,
                        'expected' => (float) $payment->amount,
                        'reported' => $reported,
                    ]);
                    $this->audit->record('payment.callback_amount_mismatch', $payment, [
                        'expected' => (float) $payment->amount,
                        'reported' => $reported,
                    ]);

                    return;
                }
            }

            match ($status) {
                'settlement', 'success', 'capture', 'paid' => $this->markAsPaid($folio, $reservation, $payment, $payload),
                'pending' => $this->markAsPending($reservation, $payment),
                'deny', 'cancel', 'expire', 'failure' => $this->markAsFailed($reservation, $payment, $status, $payload),
                default => Log::info("PaymentGatewayService: status tidak dikenal '{$status}' untuk reservasi {$reservationRef}", [
                    'reference_no' => $transactionId,
                ]),
            };
        });
    }

    public function handlePaymentReturn(string $reservationRef): Reservation
    {
        $reservation = Reservation::where('ref', $reservationRef)->with(['folios.payments', 'primaryGuest', 'property'])->firstOrFail();

        $folio = $reservation->folios->first();

        if ($folio) {
            $folio->recalculate();
            if ((float) $folio->balance <= 0) {
                $reservation->payment_status = 'paid';
                $reservation->save();
            }
        }

        return $reservation;
    }

    protected function markAsPaid(Folio $folio, Reservation $reservation, FolioPayment $payment, array $payload): void
    {
        // Idempotency: already paid → nothing to do.
        if ($payment->status === FolioPayment::STATUS_PAID) {
            $folio->recalculate();

            return;
        }
        // State machine: only pending may become paid.
        if (! $payment->canTransitionTo(FolioPayment::STATUS_PAID)) {
            Log::warning('Illegal payment transition attempt (paid callback)', [
                'reference_no' => $payment->reference_no,
                'current_status' => $payment->status,
            ]);

            return;
        }

        $payment->status = FolioPayment::STATUS_PAID;
        $payment->gateway_payload = array_merge($payment->gateway_payload ?? [], ['status' => 'paid', 'paid_at' => now()->toISOString()]);
        $payment->save();

        $folio->recalculate();

        if ((float) $folio->balance <= 0) {
            $reservation->payment_status = 'paid';
            if (in_array($reservation->status, ['tentative'], true)) {
                $reservation->status = 'confirmed';
            }
            $reservation->save();
        }

        $this->audit->record('payment.paid', $payment, [
            'reservation_ref' => $reservation->ref,
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
        ]);
    }

    protected function markAsPending(Reservation $reservation, FolioPayment $payment): void
    {
        if ($payment->status !== FolioPayment::STATUS_PENDING) {
            return;
        }
        $reservation->payment_status = 'pending';
        $reservation->save();
    }

    protected function markAsFailed(Reservation $reservation, FolioPayment $payment, string $status, array $payload): void
    {
        if (! $payment->canTransitionTo(FolioPayment::STATUS_FAILED)) {
            Log::warning('Illegal payment transition attempt (failure callback)', [
                'reference_no' => $payment->reference_no,
                'current_status' => $payment->status,
                'callback_status' => $status,
            ]);

            return;
        }

        $payment->status = FolioPayment::STATUS_FAILED;
        $payment->gateway_payload = array_merge($payment->gateway_payload ?? [], ['status' => 'failed', 'failed_reason' => $status]);
        $payment->save();

        $folio = $payment->folio;
        $folio?->recalculate();

        if ($reservation->payment_status !== 'paid') {
            $reservation->payment_status = 'failed';
            $reservation->save();
        }

        $this->audit->record('payment.failed', $payment, [
            'reservation_ref' => $reservation->ref,
            'reason' => $status,
        ]);
    }

    protected function resolveAdapter(int $propertyId): ?PaymentAdapterInterface
    {
        $provider = $this->resolveProvider($propertyId);
        if (! $provider) {
            return null;
        }

        try {
            $adapter = $this->factory->make($provider);
            if ($adapter instanceof PaymentAdapterInterface) {
                return $adapter;
            }
        } catch (\Throwable $e) {
            Log::warning("PaymentGatewayService: gagal membuat adapter — {$e->getMessage()}");
        }

        return null;
    }

    protected function resolveProvider(int $propertyId): ?Provider
    {
        $assignment = ProviderFeatureAssignment::query()
            ->where('property_id', $propertyId)
            ->where('feature', 'booking_payment')
            ->first();

        if ($assignment?->provider) {
            return $assignment->provider;
        }

        return Provider::query()
            ->where('property_id', $propertyId)
            ->where('integration_type', 'payment')
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }
}
