<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedIn;
use App\Models\DoorLockEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ActivateRoomKeys implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedIn $event): void
    {
        $reservation = $event->reservation->loadMissing('rooms.room');

        foreach ($reservation->rooms as $reservationRoom) {
            DoorLockEvent::create([
                'property_id'    => $reservation->property_id,
                'room_id'        => $reservationRoom->room_id,
                'reservation_id' => $reservation->id,
                'guest_id'       => $reservation->primary_guest_id,
                'event_type'     => 'key_activated',
                'event_data'     => [
                    'activated_for_guest' => $reservation->primary_guest_id,
                    'valid_from'          => $reservation->check_in?->toDateString(),
                    'valid_until'         => $reservation->check_out?->toDateString(),
                ],
            ]);
        }
    }
}
