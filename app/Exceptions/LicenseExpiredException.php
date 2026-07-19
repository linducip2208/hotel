<?php

declare(strict_types=1);

namespace App\Exceptions;

final class LicenseExpiredException extends HotelException
{
    public static function forTenant(int $tenantId, string $expiredAt): self
    {
        return new self(
            message: sprintf(
                'License for tenant #%d expired on %s.',
                $tenantId,
                $expiredAt,
            ),
            errorCode: 'LICENSE_EXPIRED',
            httpStatusCode: 403,
            context: [
                'tenant_id' => $tenantId,
                'expired_at' => $expiredAt,
            ],
        );
    }

    public static function forProperty(int $propertyId, string $expiredAt): self
    {
        return new self(
            message: sprintf(
                'License for property #%d expired on %s.',
                $propertyId,
                $expiredAt,
            ),
            errorCode: 'LICENSE_EXPIRED',
            httpStatusCode: 403,
            context: [
                'property_id' => $propertyId,
                'expired_at' => $expiredAt,
            ],
        );
    }
}
