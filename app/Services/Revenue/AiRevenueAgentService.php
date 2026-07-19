<?php

namespace App\Services\Revenue;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\RateOverride;
use App\Models\RateShopperSnapshot;
use App\Models\RoomType;
use App\Services\Integrations\ProviderRegistry;
use Carbon\Carbon;

class AiRevenueAgentService
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function analyze(Property $property, Carbon $date): array
    {
        $adapter = $this->registry->forFeature($property->id, 'ai_revenue_agent');
        if (! $adapter) {
            return ['error' => 'Tidak ada AI provider yang dikonfigurasi untuk Revenue Agent. Silakan setup AI Provider terlebih dahulu.'];
        }

        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->get();

        $context = [
            'property' => $property->name,
            'date' => $date->toDateString(),
            'day_of_week' => $date->englishDayOfWeek,
            'month' => $date->monthName,
            'room_types' => [],
        ];

        foreach ($roomTypes as $rt) {
            $inventory = Inventory::where('property_id', $property->id)
                ->where('room_type_id', $rt->id)->whereDate('date', $date)->first();

            $occupancy = $inventory && $inventory->total > 0
                ? round(($inventory->sold / $inventory->total) * 100, 1) : 0;

            $competitorData = RateShopperSnapshot::where('property_id', $property->id)
                ->where('shopped_for_date', $date)->latest()->first();

            $context['room_types'][] = [
                'name' => $rt->name,
                'total_rooms' => (int) ($inventory->total ?? 0),
                'sold' => (int) ($inventory->sold ?? 0),
                'occupancy_pct' => $occupancy,
                'base_rate' => (float) ($rt->base_rate ?? 0),
                'competitor_avg' => (float) ($competitorData?->avg_competitor_rate ?? 0),
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => "You are a hotel revenue manager AI for an Indonesian hotel. Analyze this hotel data and suggest optimal pricing for {$date->toDateString()}. Reply ONLY in JSON format (no markdown code fences) with keys: overall_strategy (aggressive/moderate/conservative), market_analysis (string in Indonesian), recommendations (array of {room_type, suggested_rate (number), adjustment_pct (number), reason (string in Indonesian)}), and risks (array of strings in Indonesian)."],
            ['role' => 'user', 'content' => json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)],
        ];

        try {
            $response = $this->registry->forFeature($property->id, 'ai_revenue_agent');
            if (! $response) {
                return ['error' => 'AI provider tidak tersedia.'];
            }
            $r = $response->chat($messages, options: ['max_tokens' => 2000, 'temperature' => 0.3]);
            $content = $r['content'] ?? '';
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($content));
            $parsed = json_decode($content, true);
            if (! is_array($parsed)) {
                return ['error' => 'Gagal mem-parsing respons AI.', 'raw' => $content];
            }
            return $parsed;
        } catch (\Throwable $e) {
            \Log::error("AI Revenue Agent error: {$e->getMessage()}");
            return ['error' => 'Gagal menghubungi AI: ' . $e->getMessage()];
        }
    }

    public function applyRecommendations(Property $property, Carbon $date, array $recommendations): int
    {
        $applied = 0;
        foreach ($recommendations as $rec) {
            $roomType = RoomType::where('property_id', $property->id)
                ->where('name', $rec['room_type'] ?? '')->first();
            if (! $roomType) {
                continue;
            }

            RateOverride::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'room_type_id' => $roomType->id,
                    'override_date' => $date->toDateString(),
                ],
                [
                    'price' => $rec['suggested_rate'] ?? 0,
                    'reason' => 'AI Revenue Agent: ' . ($rec['reason'] ?? 'optimasi'),
                ]
            );
            $applied++;
        }
        return $applied;
    }

    public function getWeeklyInsights(Property $property): array
    {
        $insights = [];
        for ($d = 0; $d < 7; $d++) {
            $date = now()->addDays($d);
            $insights[$date->toDateString()] = $this->analyze($property, $date);
        }
        return $insights;
    }
}
