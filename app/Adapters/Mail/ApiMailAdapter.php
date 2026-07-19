<?php

namespace App\Adapters\Mail;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\MailAdapterInterface;

class ApiMailAdapter extends BaseAdapter implements MailAdapterInterface
{
    public function send(string $to, string $subject, string $html, array $options = []): array
    {
        $endpoint = data_get($this->provider->extra_config, 'send_endpoint', 'emails');
        $from = $options['from'] ?? data_get($this->provider->extra_config, 'from_address');

        $response = $this->http->post($endpoint, [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
            'json' => [
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
            ],
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
