<?php

declare(strict_types=1);

namespace App\Exceptions;

final class TenantProvisionException extends HotelException
{
    public static function databaseCreationFailed(
        string $tenantId,
        string $reason = '',
    ): self {
        return new self(
            message: sprintf(
                'Failed to create database for tenant "%s": %s',
                $tenantId,
                $reason ?: 'unknown error',
            ),
            errorCode: 'TENANT_PROVISION_FAILED',
            httpStatusCode: 500,
            context: [
                'tenant_id' => $tenantId,
                'reason' => $reason,
            ],
        );
    }

    public static function migrationFailed(string $tenantId, string $reason = ''): self
    {
        return new self(
            message: sprintf(
                'Migration failed for tenant "%s": %s',
                $tenantId,
                $reason ?: 'unknown error',
            ),
            errorCode: 'TENANT_PROVISION_FAILED',
            httpStatusCode: 500,
            context: [
                'tenant_id' => $tenantId,
                'reason' => $reason,
            ],
        );
    }

    public static function domainConfigurationFailed(
        string $tenantId,
        string $domain,
        string $reason = '',
    ): self {
        return new self(
            message: sprintf(
                'Domain configuration failed for tenant "%s" (%s): %s',
                $tenantId,
                $domain,
                $reason ?: 'unknown error',
            ),
            errorCode: 'TENANT_PROVISION_FAILED',
            httpStatusCode: 500,
            context: [
                'tenant_id' => $tenantId,
                'domain' => $domain,
                'reason' => $reason,
            ],
        );
    }

    public static function seedingFailed(int $tenantId, string $reason = ''): self
    {
        return new self(
            message: sprintf(
                'Seeding failed for tenant #%d: %s',
                $tenantId,
                $reason ?: 'unknown error',
            ),
            errorCode: 'TENANT_PROVISION_FAILED',
            httpStatusCode: 500,
            context: [
                'tenant_id' => $tenantId,
                'reason' => $reason,
            ],
        );
    }
}
