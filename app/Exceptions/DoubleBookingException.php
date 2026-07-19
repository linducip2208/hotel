<?php

declare(strict_types=1);

namespace App\Exceptions;

final class DoubleBookingException extends HotelException
{
    public static function forRoom(
        int $roomId,
        int $existingReservationId,
        string $checkIn,
        string $checkOut,
    ): self {
        return new self(
            message: sprintf(
                'Double booking detected: room #%d is already booked (reservation #%d) for %s ~ %s.',
                $roomId,
                $existingReservationId,
                $checkIn,
                $checkOut,
            ),
            errorCode: 'DOUBLE_BOOKING',
            httpStatusCode: 409,
            context: [
                'room_id' => $roomId,
                'existing_reservation_id' => $existingReservationId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
            ],
        );
    }

    public static function forConflictingReservations(
        int $reservationId,
        int $conflictingReservationId,
        int $roomId,
    ): self {
        return new self(
            message: sprintf(
                'Double booking: reservation #%d conflicts with reservation #%d for room #%d.',
                $reservationId,
                $conflictingReservationId,
                $roomId,
            ),
            errorCode: 'DOUBLE_BOOKING',
            httpStatusCode: 409,
            context: [
                'reservation_id' => $reservationId,
                'conflicting_reservation_id' => $conflictingReservationId,
                'room_id' => $roomId,
            ],
        );
    }
}
