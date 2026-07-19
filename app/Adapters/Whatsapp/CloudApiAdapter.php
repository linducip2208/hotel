<?php

namespace App\Adapters\Whatsapp;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\WhatsappAdapterInterface;

class CloudApiAdapter extends BaseAdapter implements WhatsappAdapterInterface
{
    public function send(string $to, string $template, array $variables = []): array
    {
        $phoneId = data_get($this->provider->extra_config, 'phone_number_id');
        $response = $this->http->post("v20.0/{$phoneId}/messages", [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $variables['language'] ?? 'id'],
                    'components' => $variables['components'] ?? [],
                ],
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
