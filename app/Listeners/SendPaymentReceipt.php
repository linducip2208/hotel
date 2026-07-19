<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolioPaymentReceived;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendPaymentReceipt implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(FolioPaymentReceived $event, NotificationDispatcher $dispatcher): void
    {
        $folio   = $event->folio->loadMissing('guest', 'reservation', 'property');
        $payment = $event->folioPayment;

        if (! $folio->guest?->email) {
            return;
        }

        $key = "payment_receipt:{$payment->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->paymentReceipt($folio, $payment);

        NotificationLog::create([
            'property_id'     => $folio->property_id,
            'channel'         => 'mail',
            'event'           => 'payment_receipt',
            'recipient'       => $folio->guest->email,
            'notifiable_type' => get_class($payment),
            'notifiable_id'   => $payment->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
