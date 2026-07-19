<?php

namespace App\Adapters\Whatsapp;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\WhatsappAdapterInterface;

class OnPremAdapter extends BaseAdapter implements WhatsappAdapterInterface
{
    public function send(string $to, string $template, array $variables = []): array
    {
        $response = $this->http->post('v1/messages', [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
            'json' => [
                'to' => $to,
                'type' => 'template',
                'template' => ['name' => $template, 'parameters' => $variables['parameters'] ?? []],
            ],
        ]);
        return [
            'ok' => $response->getStatusCode() < 400,
            'raw' => json_decode((string) $response->getBody(), true) ?? [],
        ];
    }

    public function test(): array
    {
        return ['ok' => (bool) $this->apiKey(), 'message' => 'On-prem'];
    }
}
