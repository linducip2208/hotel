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

class SendPostStayFollowupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $delay = 3600; // send 1 hour after checkout

    public function __construct(public int $reservationId) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $reservation = Reservation::with('primaryGuest', 'property')->findOrFail($this->reservationId);

        if ($reservation->status !== 'checked_out') {
            return;
        }

        // Don't send if guest already reviewed
        if ($reservation->reviews()->exists()) {
            return;
        }

        $key = "post_stay_followup:{$reservation->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->reviewRequest($reservation);

        NotificationLog::create([
            'property_id'     => $reservation->property_id,
            'channel'         => 'mail',
            'event'           => 'post_stay',
            'recipient'       => $reservation->primaryGuest?->email ?? 'unknown',
            'notifiable_type' => Reservation::class,
            'notifiable_id'   => $reservation->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
