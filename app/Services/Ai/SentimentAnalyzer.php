<?php

namespace App\Services\Ai;

use App\Services\Integrations\ProviderRegistry;

class SentimentAnalyzer
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function classify(int $propertyId, string $text): string
    {
        $adapter = $this->registry->forFeature($propertyId, 'ai_sentiment');
        if (! $adapter) {
            return 'neutral';
        }

        $messages = [
            ['role' => 'system', 'content' => 'Classify message sentiment. Reply with one word: positive, neutral, or negative.'],
            ['role' => 'user', 'content' => $text],
        ];

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 5, 'temperature' => 0]);
            $reply = strtolower(trim($r['content'] ?? 'neutral'));
            return in_array($reply, ['positive', 'neutral', 'negative']) ? $reply : 'neutral';
        } catch (\Throwable $e) {
            return 'neutral';
        }
    }
}
