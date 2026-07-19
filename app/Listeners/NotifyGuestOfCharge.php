<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolioCharged;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class NotifyGuestOfCharge implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 2;

    public function handle(FolioCharged $event, NotificationDispatcher $dispatcher): void
    {
        $folio = $event->folio->loadMissing('guest', 'reservation', 'property');
        $charge = $event->folioCharge;

        if (! $folio->guest?->email) {
            return;
        }

        $key = "folio_charge_notify:{$charge->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->folioChargeNotification($folio, $charge);

        NotificationLog::create([
            'property_id'     => $folio->property_id,
            'channel'         => 'mail',
            'event'           => 'folio_charged',
            'recipient'       => $folio->guest->email,
            'notifiable_type' => get_class($folio),
            'notifiable_id'   => $folio->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
