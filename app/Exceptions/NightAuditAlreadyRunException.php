<?php

declare(strict_types=1);

namespace App\Exceptions;

final class NightAuditAlreadyRunException extends HotelException
{
    public static function forDate(int $propertyId, string $date): self
    {
        return new self(
            message: sprintf(
                'Night audit has already been run for property #%d on %s.',
                $propertyId,
                $date,
            ),
            errorCode: 'NIGHT_AUDIT_ALREADY_RUN',
            httpStatusCode: 422,
            context: [
                'property_id' => $propertyId,
                'date' => $date,
            ],
        );
    }

    public static function forAudit(int $nightAuditId): self
    {
        return new self(
            message: sprintf('Night audit #%d has already been completed.', $nightAuditId),
            errorCode: 'NIGHT_AUDIT_ALREADY_RUN',
            httpStatusCode: 422,
            context: ['night_audit_id' => $nightAuditId],
        );
    }
}
