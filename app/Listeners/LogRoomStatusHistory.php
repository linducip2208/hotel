<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RoomStatusChanged;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogRoomStatusHistory implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RoomStatusChanged $event): void
    {
        AuditLog::create([
            'property_id' => $event->room->property_id,
            'entity_type' => 'room',
            'entity_id'   => $event->room->id,
            'action'      => 'status_changed',
            'description' => sprintf(
                'Room %s status changed from "%s" to "%s"',
                $event->room->room_number ?? $event->room->id,
                $event->oldStatus,
                $event->newStatus,
            ),
            'metadata'    => [
                'room_id'         => $event->room->id,
                'room_number'     => $event->room->room_number,
                'old_status'      => $event->oldStatus,
                'new_status'      => $event->newStatus,
                'changed_by'      => $event->changedByUserId,
            ],
        ]);
    }
}
