<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Models\ReservationRoom;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ReleaseRoomInventory implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCancelled $event): void
    {
        $reservation = $event->reservation;

        ReservationRoom::where('reservation_id', $reservation->id)
            ->whereNotIn('status', ['cancelled', 'released'])
            ->update([
                'status'       => 'released',
                'released_at'  => now(),
            ]);
    }
}
