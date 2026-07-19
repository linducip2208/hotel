<?php

declare(strict_types=1);

namespace App\Support;

enum ReservationStatus: string
{
    case Tentative = 'tentative';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function isActive(): bool
    {
        return in_array($this, [self::Confirmed, self::CheckedIn], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::CheckedOut, self::Cancelled, self::NoShow], true);
    }

    public function canCheckIn(): bool
    {
        return $this === self::Confirmed;
    }

    public function label(): string
    {
        return match ($this) {
            self::Tentative => 'Tentative',
            self::Confirmed => 'Confirmed',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No Show',
        };
    }
}
