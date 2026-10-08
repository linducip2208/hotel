<?php

namespace App\Services\Fo;

/** Room type has no available inventory on a date (sold out). */
class RoomSoldOutException extends ReservationValidationException
{
    public function __construct(public readonly string $date)
    {
        parent::__construct("Kamar tidak tersedia pada tanggal {$date}.");
    }
}
