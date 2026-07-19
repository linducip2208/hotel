<?php

declare(strict_types=1);

namespace App\Exceptions;

final class PaymentFailedException extends HotelException
{
    public static function forFolio(
        int $folioId,
        string $reason = '',
        ?string $gatewayResponse = null,
    ): self {
        $message = $reason
            ? sprintf('Payment failed for folio #%d: %s', $folioId, $reason)
            : sprintf('Payment failed for folio #%d.', $folioId);

        return new self(
            message: $message,
            errorCode: 'PAYMENT_FAILED',
            httpStatusCode: 402,
            context: [
                'folio_id' => $folioId,
                'reason' => $reason,
                'gateway_response' => $gatewayResponse,
            ],
        );
    }

    public static function forReservation(
        int $reservationId,
        string $reason = '',
        ?string $gatewayResponse = null,
    ): self {
        return new self(
            message: sprintf(
                'Payment failed for reservation #%d: %s',
                $reservationId,
                $reason ?: 'unknown error',
            ),
            errorCode: 'PAYMENT_FAILED',
            httpStatusCode: 402,
            context: [
                'reservation_id' => $reservationId,
                'reason' => $reason,
                'gateway_response' => $gatewayResponse,
            ],
        );
    }

    public static function insufficientBalance(int $folioId, int $required, int $available): self
    {
        return new self(
            message: sprintf(
                'Insufficient balance for folio #%d: required %d, available %d.',
                $folioId,
                $required,
                $available,
            ),
            errorCode: 'PAYMENT_FAILED',
            httpStatusCode: 402,
            context: [
                'folio_id' => $folioId,
                'required' => $required,
                'available' => $available,
            ],
        );
    }
}
