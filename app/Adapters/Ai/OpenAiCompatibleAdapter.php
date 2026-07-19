<?php

namespace App\Adapters\Ai;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\AiAdapterInterface;

class OpenAiCompatibleAdapter extends BaseAdapter implements AiAdapterInterface
{
    public function chat(array $messages, ?string $model = null, array $options = []): array
    {
        $response = $this->http->post('chat/completions', [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
            'json' => array_merge([
                'model' => $model ?? $this->provider->default_model ?? 'gpt-4o-mini',
                'messages' => $messages,
            ], $options),
        ]);

        $data = json_decode((string) $response->getBody(), true) ?? [];
        return [
            'ok' => $response->getStatusCode() === 200,
            'status' => $response->getStatusCode(),
            'content' => data_get($data, 'choices.0.message.content'),
            'usage' => $data['usage'] ?? [],
            'raw' => $data,
        ];
    }

    public function listModels(): array
    {
        $response = $this->http->get('models', [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey()],
        ]);
        $data = json_decode((string) $response->getBody(), true) ?? [];
        return $data['data'] ?? [];
    }

    public function test(): array
    {
        $r = $this->chat([['role' => 'user', 'content' => 'ping']], options: ['max_tokens' => 5]);
        return ['ok' => $r['ok'] ?? false, 'message' => $r['ok'] ? 'OK' : ('HTTP '.$r['status'])];
    }
}
