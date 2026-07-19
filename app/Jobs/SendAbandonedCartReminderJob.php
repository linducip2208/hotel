<?php

namespace App\Jobs;

use App\Models\AbandonedCart;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendAbandonedCartReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $sessionId,
        public string $guestEmail,
        public string $guestName,
        public array $cartData,
        public string $recoveryToken,
    ) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $key = "abandon_{$this->sessionId}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $cart = AbandonedCart::where('session_id', $this->sessionId)->first();
        if (! $cart || $cart->recovered_at || $cart->expires_at->isPast()) {
            return;
        }

        $recoveryUrl = URL::signedRoute('booking.cart.recover', ['token' => $this->recoveryToken], now()->addHours(24));

        $html = $this->buildEmailHtml($recoveryUrl);

        try {
            $adapter = app(\App\Services\Integrations\ProviderRegistry::class)
                ->forFeature(null, 'mail_transactional');

            if ($adapter) {
                $adapter->send($this->guestEmail, 'Your booking cart is waiting — Complete your reservation', $html);
            } else {
                Log::info("Abandon cart reminder (no provider): to={$this->guestEmail} session={$this->sessionId}");
            }
        } catch (\Throwable $e) {
            Log::warning("Abandon cart email failed: {$e->getMessage()}");
        }

        NotificationLog::create([
            'channel' => 'mail',
            'event' => 'abandoned_cart',
            'recipient' => $this->guestEmail,
            'status' => 'sent',
            'idempotency_key' => $key,
            'sent_at' => now(),
        ]);
    }

    protected function buildEmailHtml(string $recoveryUrl): string
    {
        $name = $this->guestName ?: 'Guest';
        $items = $this->cartData['items'] ?? [];
        $itemsHtml = '';
        foreach ($items as $item) {
            $label = $item['name'] ?? $item['room_type'] ?? 'Room';
            $price = $item['price'] ?? $item['total_price'] ?? 0;
            $itemsHtml .= "<li>{$label} — Rp " . number_format((float) $price, 0, ',', '.') . "</li>";
        }

        return <<<HTML
<p>Halo {$name},</p>
<p>Sepertinya Anda belum menyelesaikan pemesanan. Keranjang Anda masih tersimpan:</p>
<ul>{$itemsHtml}</ul>
<p>Lanjutkan pemesanan dengan klik tombol di bawah:</p>
<p style="text-align:center;">
    <a href="{$recoveryUrl}" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block;font-weight:600;">Lanjutkan Pemesanan</a>
</p>
<p style="color:#666;font-size:12px;">Link berlaku 24 jam. Jika Anda sudah menyelesaikan pemesanan, abaikan email ini.</p>
HTML;
    }
}
