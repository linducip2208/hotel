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

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $reservationId) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $reservation = Reservation::with('primaryGuest', 'property')->findOrFail($this->reservationId);

        $key = "booking_confirmed:{$reservation->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return; // already sent
        }

        $dispatcher->bookingConfirmed($reservation);

        NotificationLog::create([
            'property_id'       => $reservation->property_id,
            'channel'           => 'mail',
            'event'             => 'booking_confirmed',
            'recipient'         => $reservation->primaryGuest?->email ?? 'unknown',
            'notifiable_type'   => Reservation::class,
            'notifiable_id'     => $reservation->id,
            'status'            => 'sent',
            'idempotency_key'   => $key,
            'sent_at'           => now(),
        ]);
    }
}
