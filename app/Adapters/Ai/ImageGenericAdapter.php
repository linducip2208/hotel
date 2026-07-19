<?php

namespace App\Adapters\Ai;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\AdapterInterface;

class ImageGenericAdapter extends BaseAdapter implements AdapterInterface
{
    public function generate(string $prompt, array $options = []): array
    {
        $endpoint = data_get($this->provider->extra_config, 'image_endpoint', 'images/generations');
        $response = $this->http->post($endpoint, [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
            'json' => array_merge(['prompt' => $prompt, 'n' => 1, 'size' => '1024x1024'], $options),
        ]);
        return [
            'ok' => $response->getStatusCode() === 200,
            'data' => json_decode((string) $response->getBody(), true) ?? [],
        ];
    }

    public function test(): array
    {
        $r = $this->generate('ping');
        return ['ok' => $r['ok'] ?? false, 'message' => $r['ok'] ? 'OK' : 'Failed'];
    }
}
