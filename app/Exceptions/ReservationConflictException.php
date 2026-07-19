<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ReservationConflictException extends HotelException
{
    public static function forOverlappingDates(
        int $roomId,
        string $checkIn,
        string $checkOut,
        ?int $conflictingReservationId = null,
    ): self {
        $message = $conflictingReservationId
            ? sprintf(
                'Room #%d is already reserved (reservation #%d) for the period %s ~ %s.',
                $roomId,
                $conflictingReservationId,
                $checkIn,
                $checkOut,
            )
            : sprintf(
                'Room #%d is not available for the period %s ~ %s.',
                $roomId,
                $checkIn,
                $checkOut,
            );

        return new self(
            message: $message,
            errorCode: 'RESERVATION_CONFLICT',
            httpStatusCode: 409,
            context: [
                'room_id' => $roomId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'conflicting_reservation_id' => $conflictingReservationId,
            ],
        );
    }

    public static function forRoomTypeOverbooking(
        int $roomTypeId,
        string $date,
        int $requested,
        int $available,
    ): self {
        return new self(
            message: sprintf(
                'Room type #%d has only %d room(s) available for %s (%d requested).',
                $roomTypeId,
                $available,
                $date,
                $requested,
            ),
            errorCode: 'RESERVATION_CONFLICT',
            httpStatusCode: 409,
            context: [
                'room_type_id' => $roomTypeId,
                'date' => $date,
                'requested' => $requested,
                'available' => $available,
            ],
        );
    }
}
