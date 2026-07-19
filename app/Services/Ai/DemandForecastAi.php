<?php

namespace App\Services\Ai;

use App\Models\Property;
use App\Services\Integrations\ProviderRegistry;
use App\Services\Rms\DemandForecaster;
use Carbon\Carbon;

class DemandForecastAi
{
    public function __construct(
        protected ProviderRegistry $registry,
        protected DemandForecaster $baseForecaster,
    ) {}

    /**
     * AI-augmented forecast: feed historical + base forecast to LLM, ask for refinement
     * considering: school holidays, religious holidays, local events, weather patterns.
     */
    public function refine(Property $property, Carbon $from, Carbon $to): array
    {
        $base = $this->baseForecaster->forecast($property, $from, $to);

        $adapter = $this->registry->forFeature($property->id, 'ai_demand_forecast');
        if (! $adapter) {
            return ['ok' => true, 'forecast' => $base, 'ai_refined' => false];
        }

        $messages = [
            ['role' => 'system', 'content' => "You are a hotel revenue manager. Given base forecast data, refine forecast considering Indonesian school holidays, religious holidays (Idul Fitri, Christmas, Imlek), and seasonal patterns for {$property->city}. Output JSON array with same shape but refined occupancy_pct and rate_modifier_pct."],
            ['role' => 'user', 'content' => json_encode($base)],
        ];

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 2000, 'temperature' => 0.3]);
            $refined = json_decode($r['content'] ?? '[]', true);
            return ['ok' => true, 'forecast' => $refined ?: $base, 'ai_refined' => (bool) $refined];
        } catch (\Throwable $e) {
            return ['ok' => true, 'forecast' => $base, 'ai_refined' => false, 'error' => $e->getMessage()];
        }
    }
}
