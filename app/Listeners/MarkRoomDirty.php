<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use App\Models\ReservationRoom;
use App\Models\Room;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class MarkRoomDirty implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedOut $event): void
    {
        $reservation = $event->reservation->loadMissing('rooms');

        $roomIds = $reservation->rooms->pluck('room_id')->unique();

        Room::whereIn('id', $roomIds)->update(['hk_status' => 'dirty']);

        ReservationRoom::where('reservation_id', $reservation->id)
            ->where('status', 'occupied')
            ->update(['status' => 'checked_out']);
    }
}
