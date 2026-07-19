<?php

namespace App\Services\Ai;

use App\Services\Integrations\ProviderRegistry;

class TranslationService
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function translate(int $propertyId, string $text, string $toLocale, ?string $fromLocale = null): array
    {
        $adapter = $this->registry->forFeature($propertyId, 'ai_translate');
        if (! $adapter) {
            return ['ok' => false, 'error' => 'No AI provider configured for translation'];
        }

        $messages = [
            ['role' => 'system', 'content' => "You are a translator. Translate the user message to {$toLocale} (ISO 639-1 code). Output only the translation, no explanation."],
            ['role' => 'user', 'content' => $text],
        ];

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 600, 'temperature' => 0.3]);
            return ['ok' => $r['ok'] ?? false, 'translation' => $r['content'] ?? null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
