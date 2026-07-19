<?php

namespace App\Adapters\Payment;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\PaymentAdapterInterface;

class RedirectFlowAdapter extends BaseAdapter implements PaymentAdapterInterface
{
    public function charge(array $payload): array
    {
        $endpoint = data_get($this->provider->extra_config, 'charge_endpoint', 'transaction');
        $response = $this->http->post($endpoint, [
            'headers' => ['Authorization' => 'Basic '.base64_encode($this->apiKey().':')],
            'json' => $payload,
        ]);
        $data = json_decode((string) $response->getBody(), true) ?? [];
        return [
            'ok' => $response->getStatusCode() < 400,
            'redirect_url' => $data['redirect_url'] ?? $data['payment_url'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? $data['id'] ?? null,
            'raw' => $data,
        ];
    }

    public function verifyCallback(array $payload, array $headers = []): bool
    {
        $signature = $payload['signature_key'] ?? $headers['x-signature'] ?? null;
        if (! $signature) return false;

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $secret = $this->provider->getSecret();

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$secret);
        return hash_equals($expected, $signature);
    }

    public function refund(string $transactionId, int $amount): array
    {
        $response = $this->http->post("v2/{$transactionId}/refund", [
            'headers' => ['Authorization' => 'Basic '.base64_encode($this->apiKey().':')],
            'json' => ['amount' => $amount, 'reason' => 'Refund'],
        ]);
        return [
            'ok' => $response->getStatusCode() < 400,
            'raw' => json_decode((string) $response->getBody(), true) ?? [],
        ];
    }

    public function test(): array
    {
        return ['ok' => (bool) $this->apiKey(), 'message' => $this->apiKey() ? 'Credentials present' : 'Missing API key'];
    }
}
