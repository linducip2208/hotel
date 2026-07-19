<?php

namespace App\Services\Ai;

use App\Models\Property;
use App\Services\Integrations\ProviderRegistry;

class ConciergeService
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function chat(Property $property, string $userMessage, array $history = [], string $locale = 'id'): array
    {
        $adapter = $this->registry->forFeature($property->id, 'ai_concierge');
        if (! $adapter) {
            return ['ok' => false, 'error' => 'No AI concierge provider'];
        }

        $systemPrompt = $this->buildSystemPrompt($property, $locale);
        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $userMessage]]
        );

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 800, 'temperature' => 0.7]);
            return ['ok' => $r['ok'] ?? false, 'reply' => $r['content'] ?? null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function buildSystemPrompt(Property $p, string $locale): string
    {
        $base = "You are a virtual concierge for {$p->name} located at {$p->city}, {$p->province}, Indonesia. " .
                "Star rating: {$p->star_rating}. Check-in {$p->check_in_time?->format('H:i')}, check-out {$p->check_out_time?->format('H:i')}. " .
                "Reply in {$locale} (ISO 639-1). Be friendly, helpful, concise. " .
                "If asked about reservation, ask for booking ref to look up. " .
                "If asked about local recommendations, give practical advice (transport, food, attractions). " .
                "Don't make up policies — say 'let me check with our front desk' for hotel-specific questions you're unsure about.";
        return $base;
    }
}
