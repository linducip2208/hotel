<?php

namespace App\Adapters\Ai;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\AiAdapterInterface;

class AnthropicAdapter extends BaseAdapter implements AiAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'x-api-key' => $this->apiKey() ?? '',
            'anthropic-version' => '2023-06-01',
        ]);
    }

    public function chat(array $messages, ?string $model = null, array $options = []): array
    {
        $system = null;
        $cleaned = [];
        foreach ($messages as $m) {
            if (($m['role'] ?? null) === 'system') {
                $system = $m['content'];
            } else {
                $cleaned[] = $m;
            }
        }

        $payload = [
            'model' => $model ?? $this->provider->default_model ?? 'claude-haiku-4-5-20251001',
            'messages' => $cleaned,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ];
        if ($system) $payload['system'] = $system;

        $response = $this->http->post('v1/messages', ['json' => $payload]);
        $data = json_decode((string) $response->getBody(), true) ?? [];

        return [
            'ok' => $response->getStatusCode() === 200,
            'status' => $response->getStatusCode(),
            'content' => data_get($data, 'content.0.text'),
            'usage' => $data['usage'] ?? [],
            'raw' => $data,
        ];
    }

    public function listModels(): array
    {
        return [];
    }

    public function test(): array
    {
        $r = $this->chat([['role' => 'user', 'content' => 'ping']], options: ['max_tokens' => 5]);
        return ['ok' => $r['ok'] ?? false, 'message' => $r['ok'] ? 'OK' : ('HTTP '.$r['status'])];
    }
}
