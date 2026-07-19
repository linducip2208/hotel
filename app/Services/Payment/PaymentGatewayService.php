<?php

namespace App\Services\Payment;

use App\Adapters\Contracts\PaymentAdapterInterface;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use App\Models\Reservation;
use App\Services\Fo\FolioService;
use App\Services\Integrations\AdapterFactory;
use App\Services\Integrations\ProviderRegistry;
use App\Services\Notifications\NotificationDispatcher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    public function __construct(
        protected ProviderRegistry $registry,
        protected AdapterFactory $factory,
        protected FolioService $folioService,
        protected NotificationDispatcher $dispatcher,
    ) {}

    /**
     * Buat transaksi pembayaran untuk reservasi.
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
        $amount = (int) round((float) $folio->balance);

        if ($amount <= 0) {
            return ['ok' => true, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Saldo sudah lunas'];
        }

        $adapter = $this->resolveAdapter($reservation->property_id);
        $provider = $this->resolveProvider($reservation->property_id);

        if (! $adapter) {
            return ['ok' => false, 'redirect_url' => null, 'transaction_id' => null, 'payment_method' => $method, 'raw' => [], 'error' => 'Belum ada payment gateway yang dikonfigurasi. Silakan tambahkan provider di Pengaturan → Payment Gateway.'];
        }

        $transactionId = 'PAY-' . $reservation->ref . '-' . now()->format('His');

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
                'name' => 'Reservasi Hotel - ' . $reservation->ref,
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
                    'provider_id' => $provider?->id,
                    'reference_no' => $result['transaction_id'] ?? $transactionId,
                    'gateway_payload' => ['txn_id' => $result['transaction_id'] ?? $transactionId, 'method' => $method, 'provider' => $provider?->name, 'status' => 'pending'],
                    'cashier_id' => null,
                ]);
                $folio->recalculate();
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
        if (! $reservation) return false;

        $adapter = $this->resolveAdapter($reservation->property_id);
        if (! $adapter) return false;

        return $adapter->verifyCallback($payload, $headers);
    }

    public function handleCallback(string $reservationRef, string $status, array $payload = []): void
    {
        DB::transaction(function () use ($reservationRef, $status, $payload) {
            $reservation = Reservation::where('ref', $reservationRef)->with('folios')->firstOrFail();
            $folio = $reservation->folios->first();
            if (! $folio) return;

            $transactionId = $payload['order_id'] ?? $payload['transaction_id'] ?? null;

            match ($status) {
                'settlement', 'success', 'capture', 'paid' => $this->markAsPaid($folio, $reservation, $transactionId, $payload),
                'pending' => $this->markAsPending($folio, $transactionId, $payload),
                'deny', 'cancel', 'expire', 'failure' => $this->markAsFailed($folio, $transactionId, $status, $payload),
                default => Log::info("PaymentGatewayService: status tidak dikenal '{$status}' untuk reservasi {$reservationRef}"),
            };
        });
    }

    public function handlePaymentReturn(string $reservationRef): Reservation
    {
        $reservation = Reservation::where('ref', $reservationRef)->with(['folios.payments', 'primaryGuest', 'property'])->firstOrFail();

        $folio = $reservation->folios->first();

        if ($folio && (float) $folio->balance <= 0) {
            $reservation->payment_status = 'paid';
            $reservation->save();
        }

        return $reservation;
    }

    protected function markAsPaid(Folio $folio, Reservation $reservation, ?string $transactionId, array $payload): void
    {
        $folio->payments()
            ->where('reference_no', $transactionId)
            ->where('gateway_payload->status', 'pending')
            ->update(['gateway_payload->status' => 'paid']);

        $folio->recalculate();

        if ((float) $folio->balance <= 0) {
            $reservation->payment_status = 'paid';
            $reservation->status = 'confirmed';
            $reservation->save();
        }
    }

    protected function markAsPending(Folio $folio, ?string $transactionId, array $payload): void
    {
        $reservation = $folio->reservation;
        if ($reservation) {
            $reservation->payment_status = 'pending';
            $reservation->save();
        }
    }

    protected function markAsFailed(Folio $folio, ?string $transactionId, string $status, array $payload): void
    {
        $folio->payments()
            ->where('reference_no', $transactionId)
            ->where('gateway_payload->status', 'pending')
            ->update(['gateway_payload->status' => 'failed']);

        $reservation = $folio->reservation;
        if ($reservation) {
            $reservation->payment_status = 'failed';
            $reservation->save();
        }
    }

    protected function resolveAdapter(int $propertyId): ?PaymentAdapterInterface
    {
        $provider = $this->resolveProvider($propertyId);
        if (! $provider) return null;

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
