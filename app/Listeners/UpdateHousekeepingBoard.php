<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RoomStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UpdateHousekeepingBoard implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RoomStatusChanged $event): void
    {
        // Real-time board refresh - in a full implementation this would
        // broadcast via WebSockets to the housekeeping dashboard.
        // For now, the room status is directly updated in the database,
        // and the frontend polls or receives broadcast updates.
    }
}
