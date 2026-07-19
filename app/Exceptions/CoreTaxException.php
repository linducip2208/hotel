<?php

declare(strict_types=1);

namespace App\Exceptions;

final class CoreTaxException extends HotelException
{
    public static function apiError(string $endpoint, array $response, ?\Throwable $previous = null): self
    {
        $message = $response['error'] ?? $response['pesan'] ?? 'Coretax API error';
        return new self(
            message: sprintf('Coretax %s: %s', $endpoint, $message),
            errorCode: 'CORETAX_API_ERROR',
            httpStatusCode: 502,
            context: [
                'endpoint' => $endpoint,
                'response' => $response,
            ],
            previous: $previous,
        );
    }

    public static function invoiceValidationFailed(string $reason, array $details = []): self
    {
        return new self(
            message: sprintf('Invoice validation failed: %s', $reason),
            errorCode: 'CORETAX_VALIDATION_FAILED',
            httpStatusCode: 422,
            context: array_merge(['reason' => $reason], $details),
        );
    }

    public static function certificateError(string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            message: sprintf('Digital certificate error: %s', $reason),
            errorCode: 'CORETAX_CERT_ERROR',
            httpStatusCode: 500,
            context: ['reason' => $reason],
            previous: $previous,
        );
    }

    public static function networkError(string $endpoint, string $error, ?\Throwable $previous = null): self
    {
        return new self(
            message: sprintf('Network error calling Coretax %s: %s', $endpoint, $error),
            errorCode: 'CORETAX_NETWORK_ERROR',
            httpStatusCode: 502,
            context: ['endpoint' => $endpoint, 'error' => $error],
            previous: $previous,
        );
    }

    public static function npwpInvalid(string $npwp): self
    {
        return new self(
            message: sprintf('NPWP %s is invalid or not registered at DJP.', $npwp),
            errorCode: 'CORETAX_NPWP_INVALID',
            httpStatusCode: 422,
            context: ['npwp' => $npwp],
        );
    }

    public static function notConfigured(): self
    {
        return new self(
            message: 'Coretax integration is not configured. Set CORETAX_BASE_URL and CORETAX_CERT_PASSWORD in .env.',
            errorCode: 'CORETAX_NOT_CONFIGURED',
            httpStatusCode: 500,
        );
    }
}
