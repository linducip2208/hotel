<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Room;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Room $room,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        public readonly ?int $changedByUserId = null,
    ) {}
}
