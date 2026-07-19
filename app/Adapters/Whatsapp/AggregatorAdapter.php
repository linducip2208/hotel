<?php

namespace App\Adapters\Whatsapp;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\WhatsappAdapterInterface;

class AggregatorAdapter extends BaseAdapter implements WhatsappAdapterInterface
{
    public function send(string $to, string $template, array $variables = []): array
    {
        $response = $this->http->post('whatsapp/messages', [
            'headers' => ['X-API-KEY' => $this->apiKey()],
            'json' => [
                'phone' => $to,
                'template' => $template,
                'variables' => $variables,
            ],
        ]);
        return [
            'ok' => $response->getStatusCode() < 400,
            'raw' => json_decode((string) $response->getBody(), true) ?? [],
        ];
    }

    public function test(): array
    {
        return ['ok' => (bool) $this->apiKey(), 'message' => 'Aggregator'];
    }
}
