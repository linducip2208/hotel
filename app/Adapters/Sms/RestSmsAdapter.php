<?php

namespace App\Adapters\Sms;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\SmsAdapterInterface;

class RestSmsAdapter extends BaseAdapter implements SmsAdapterInterface
{
    public function send(string $to, string $message): array
    {
        $endpoint = data_get($this->provider->extra_config, 'send_endpoint', 'messages');
        $field = data_get($this->provider->extra_config, 'fields', [
            'to' => 'to',
            'from' => 'from',
            'body' => 'body',
        ]);

        $body = [
            $field['to'] => $to,
            $field['body'] => $message,
        ];
        if ($from = data_get($this->provider->extra_config, 'sender_id')) {
            $body[$field['from']] = $from;
        }

        $authMode = data_get($this->provider->extra_config, 'auth_mode', 'bearer');
        $headers = $authMode === 'basic'
            ? ['Authorization' => 'Basic '.base64_encode($this->apiKey().':'.$this->provider->getSecret())]
            : ['Authorization' => 'Bearer '.$this->apiKey()];

        $response = $this->http->post($endpoint, ['headers' => $headers, 'json' => $body]);

        return [
            'ok' => $response->getStatusCode() < 400,
            'status' => $response->getStatusCode(),
            'raw' => json_decode((string) $response->getBody(), true) ?? [],
        ];
    }

    public function test(): array
    {
        return ['ok' => (bool) $this->apiKey(), 'message' => $this->apiKey() ? 'Credentials present' : 'Missing API key'];
    }
}
