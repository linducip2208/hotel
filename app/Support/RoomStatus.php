<?php

declare(strict_types=1);

namespace App\Support;

enum RoomStatus: string
{
    case Clean = 'clean';
    case Dirty = 'dirty';
    case Inspected = 'inspected';
    case Pickup = 'pickup';
    case OutOfOrder = 'out_of_order';
    case OutOfService = 'out_of_service';

    public function isOccupiable(): bool
    {
        return in_array($this, [self::Clean, self::Inspected], true);
    }

    public function isHousekeeping(): bool
    {
        return in_array($this, [self::Dirty, self::Pickup], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Clean => 'Clean',
            self::Dirty => 'Dirty',
            self::Inspected => 'Inspected',
            self::Pickup => 'Pickup',
            self::OutOfOrder => 'Out of Order',
            self::OutOfService => 'Out of Service',
        };
    }
}
