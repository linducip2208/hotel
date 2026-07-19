<?php

namespace App\Services\Ai;

use App\Models\Review;
use App\Services\Integrations\ProviderRegistry;

class ReviewReplyGenerator
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function generate(Review $review, string $tone = 'friendly_professional', string $locale = 'id'): array
    {
        $adapter = $this->registry->forFeature($review->property_id, 'ai_review_reply');
        if (! $adapter) {
            return ['ok' => false, 'error' => 'No AI provider configured'];
        }

        $rating = $review->rating ?? 5;
        $sentiment = $rating >= 4 ? 'positive' : ($rating >= 3 ? 'neutral' : 'negative');

        $messages = [
            ['role' => 'system', 'content' => "You are a hotel manager replying to a guest review. Tone: {$tone}. Locale: {$locale}. Sentiment: {$sentiment}. Acknowledge feedback genuinely. For negative reviews, apologize and offer concrete remediation. Keep reply 60-120 words."],
            ['role' => 'user', 'content' => "Review (rating {$rating}/5): {$review->comment}"],
        ];

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 400, 'temperature' => 0.6]);
            return ['ok' => $r['ok'] ?? false, 'reply' => $r['content'] ?? null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
