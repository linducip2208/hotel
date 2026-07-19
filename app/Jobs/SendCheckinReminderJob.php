<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Reservation;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCheckinReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $reservationId) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $reservation = Reservation::with('primaryGuest', 'property')->findOrFail($this->reservationId);

        if (! in_array($reservation->status, ['confirmed', 'tentative'])) {
            return; // cancelled/checked-in already — skip
        }

        $key = "checkin_reminder:{$reservation->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->checkinReminder($reservation);

        NotificationLog::create([
            'property_id'     => $reservation->property_id,
            'channel'         => 'mail',
            'event'           => 'checkin_reminder',
            'recipient'       => $reservation->primaryGuest?->email ?? 'unknown',
            'notifiable_type' => Reservation::class,
            'notifiable_id'   => $reservation->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
