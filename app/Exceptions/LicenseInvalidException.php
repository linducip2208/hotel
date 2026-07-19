<?php

declare(strict_types=1);

namespace App\Exceptions;

final class LicenseInvalidException extends HotelException
{
    public static function forTenant(int $tenantId, string $reason = ''): self
    {
        $message = $reason
            ? sprintf('License for tenant #%d is invalid: %s', $tenantId, $reason)
            : sprintf('License for tenant #%d is invalid.', $tenantId);

        return new self(
            message: $message,
            errorCode: 'LICENSE_INVALID',
            httpStatusCode: 403,
            context: [
                'tenant_id' => $tenantId,
                'reason' => $reason,
            ],
        );
    }

    public static function forProperty(int $propertyId, string $reason = ''): self
    {
        $message = $reason
            ? sprintf('License for property #%d is invalid: %s', $propertyId, $reason)
            : sprintf('License for property #%d is invalid.', $propertyId);

        return new self(
            message: $message,
            errorCode: 'LICENSE_INVALID',
            httpStatusCode: 403,
            context: [
                'property_id' => $propertyId,
                'reason' => $reason,
            ],
        );
    }

    public static function tampered(string $detail = ''): self
    {
        return new self(
            message: $detail ?: 'License key has been tampered with.',
            errorCode: 'LICENSE_INVALID',
            httpStatusCode: 403,
            context: ['detail' => $detail],
        );
    }
}
