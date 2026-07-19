<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendCancellationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(ReservationCancelled $event, NotificationDispatcher $dispatcher): void
    {
        $reservation = $event->reservation->loadMissing('primaryGuest', 'property');

        $key = "booking_cancelled:{$reservation->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->bookingCancelled($reservation, $event->reason);

        NotificationLog::create([
            'property_id'     => $reservation->property_id,
            'channel'         => 'mail',
            'event'           => 'booking_cancelled',
            'recipient'       => $reservation->primaryGuest?->email ?? 'unknown',
            'notifiable_type' => get_class($reservation),
            'notifiable_id'   => $reservation->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
