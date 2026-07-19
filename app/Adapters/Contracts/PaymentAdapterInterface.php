<?php

namespace App\Adapters\Contracts;

interface PaymentAdapterInterface extends AdapterInterface
{
    public function charge(array $payload): array;
    public function verifyCallback(array $payload, array $headers = []): bool;
    public function refund(string $transactionId, int $amount): array;
}
