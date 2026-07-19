<?php

namespace App\Adapters\Ai;

use App\Adapters\BaseAdapter;
use App\Adapters\Contracts\AiAdapterInterface;

class GeminiAdapter extends BaseAdapter implements AiAdapterInterface
{
    public function chat(array $messages, ?string $model = null, array $options = []): array
    {
        $contents = [];
        foreach ($messages as $m) {
            $contents[] = [
                'role' => ($m['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => (string) ($m['content'] ?? '')]],
            ];
        }

        $modelName = $model ?? $this->provider->default_model ?? 'gemini-1.5-flash';
        $response = $this->http->post("v1beta/models/{$modelName}:generateContent?key=".urlencode((string) $this->apiKey()), [
            'json' => ['contents' => $contents],
        ]);
        $data = json_decode((string) $response->getBody(), true) ?? [];

        return [
            'ok' => $response->getStatusCode() === 200,
            'status' => $response->getStatusCode(),
            'content' => data_get($data, 'candidates.0.content.parts.0.text'),
            'raw' => $data,
        ];
    }

    public function listModels(): array { return []; }

    public function test(): array
    {
        $r = $this->chat([['role' => 'user', 'content' => 'ping']]);
        return ['ok' => $r['ok'] ?? false, 'message' => $r['ok'] ? 'OK' : ('HTTP '.$r['status'])];
    }
}
