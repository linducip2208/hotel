<?php

declare(strict_types=1);

namespace App\Exceptions;

final class FolioAlreadySettledException extends HotelException
{
    public static function forFolio(int $folioId, ?string $settledAt = null): self
    {
        $message = $settledAt
            ? sprintf('Folio #%d was already settled on %s.', $folioId, $settledAt)
            : sprintf('Folio #%d has already been settled.', $folioId);

        return new self(
            message: $message,
            errorCode: 'FOLIO_ALREADY_SETTLED',
            httpStatusCode: 422,
            context: [
                'folio_id' => $folioId,
                'settled_at' => $settledAt,
            ],
        );
    }

    public static function forReservation(int $reservationId, int $folioId): self
    {
        return new self(
            message: sprintf(
                'Cannot modify folio #%d — reservation #%d has already been settled.',
                $folioId,
                $reservationId,
            ),
            errorCode: 'FOLIO_ALREADY_SETTLED',
            httpStatusCode: 422,
            context: [
                'reservation_id' => $reservationId,
                'folio_id' => $folioId,
            ],
        );
    }
}
