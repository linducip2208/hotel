<?php

declare(strict_types=1);

namespace App\Exceptions;

final class RateNotFoundException extends HotelException
{
    public static function forId(int $rateId): self
    {
        return new self(
            message: sprintf('Rate #%d not found.', $rateId),
            errorCode: 'RATE_NOT_FOUND',
            httpStatusCode: 404,
            context: ['rate_id' => $rateId],
        );
    }

    public static function forRoomTypeAndDate(
        int $roomTypeId,
        string $date,
    ): self {
        return new self(
            message: sprintf(
                'No rate found for room type #%d on %s.',
                $roomTypeId,
                $date,
            ),
            errorCode: 'RATE_NOT_FOUND',
            httpStatusCode: 404,
            context: [
                'room_type_id' => $roomTypeId,
                'date' => $date,
            ],
        );
    }

    public static function forRatePlan(int $ratePlanId, string $date): self
    {
        return new self(
            message: sprintf(
                'No rate found for rate plan #%d on %s.',
                $ratePlanId,
                $date,
            ),
            errorCode: 'RATE_NOT_FOUND',
            httpStatusCode: 404,
            context: [
                'rate_plan_id' => $ratePlanId,
                'date' => $date,
            ],
        );
    }
}
