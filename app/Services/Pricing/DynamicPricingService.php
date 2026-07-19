<?php

namespace App\Services\Pricing;

use App\Models\DynamicPricingLog;
use App\Models\DynamicPricingRule;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Rate;
use App\Models\RateOverride;
use App\Services\Rms\DemandForecaster;
use Carbon\Carbon;

/**
 * Dynamic Pricing Service — closed-loop engine.
 * Reads active rules for a property, evaluates metrics against thresholds,
 * and writes RateOverride records when a rule fires. Pushes to channels
 * via AriSyncService after applying (caller's responsibility to queue push).
 */
class DynamicPricingService
{
    public function __construct(
        private DemandForecaster   $forecaster,
        private OpenPricingService $openPricing,
    ) {}

    /**
     * Apply all active rules for a property over the next {lookahead_days} days.
     * Returns the number of price overrides written.
     */
    public function applyRules(Property $property): int
    {
        $rules = DynamicPricingRule::where('property_id', $property->id)
            ->where('is_active', true)
            ->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        $count = 0;
        $today = Carbon::today();

        foreach ($rules as $rule) {
            $lookahead = max(1, $rule->lookahead_days);
            $forecast  = $this->forecaster->forecast(
                $property,
                $today->copy()->addDay(),
                $today->copy()->addDays($lookahead)
            );

            foreach ($forecast as $day) {
                $metric = $this->extractMetric($day, $rule->trigger_metric, $today, $day['date']);

                if (! $this->matchesThreshold($rule, $metric)) {
                    continue;
                }

                $roomTypes = $rule->room_type_id
                    ? collect([$rule->room_type_id])
                    : $property->roomTypes()->pluck('id');

                foreach ($roomTypes as $rtId) {
                    $current = $this->openPricing->effectivePrice(
                        $property->id, $rtId, $rule->channel_id, $day['date']
                    );

                    $newPrice = $this->calculateNewPrice(
                        (float) $current['price'], $rule
                    );

                    if ($newPrice === (float) $current['price']) {
                        continue;
                    }

                    RateOverride::updateOrCreate(
                        [
                            'property_id'   => $property->id,
                            'room_type_id'  => $rtId,
                            'channel_id'    => $rule->channel_id,
                            'override_date' => $day['date'],
                        ],
                        [
                            'price'  => $newPrice,
                            'source' => 'dynamic',
                        ]
                    );

                    DynamicPricingLog::create([
                        'property_id'     => $property->id,
                        'rule_id'         => $rule->id,
                        'target_date'     => $day['date'],
                        'room_type_id'    => $rtId,
                        'channel_id'      => $rule->channel_id,
                        'price_before'    => $current['price'],
                        'price_after'     => $newPrice,
                        'trigger_reason'  => "{$rule->trigger_metric} {$rule->operator} {$metric}",
                        'metrics_snapshot'=> $day,
                    ]);

                    $count++;
                }
            }

            $rule->update(['last_applied_at' => now()]);
        }

        return $count;
    }

    private function extractMetric(array $day, string $metric, Carbon $today, string $targetDate): float
    {
        return match ($metric) {
            'occupancy_pct'   => (float) ($day['forecast_occupancy_pct'] ?? 0),
            'days_to_arrival' => (float) $today->diffInDays(Carbon::parse($targetDate)),
            default           => 0,
        };
    }

    private function matchesThreshold(DynamicPricingRule $rule, float $value): bool
    {
        return match ($rule->operator) {
            'gte'     => $value >= $rule->threshold_low,
            'lte'     => $value <= $rule->threshold_low,
            'between' => $value >= $rule->threshold_low && $value <= ($rule->threshold_high ?? PHP_FLOAT_MAX),
            default   => false,
        };
    }

    private function calculateNewPrice(float $current, DynamicPricingRule $rule): float
    {
        $val   = (float) $rule->action_value;
        $price = match ($rule->action) {
            'pct_increase'    => $current * (1 + $val / 100),
            'pct_decrease'    => $current * (1 - $val / 100),
            'fixed_increase'  => $current + $val,
            'fixed_decrease'  => $current - $val,
            default           => $current,
        };

        if ($rule->min_price_floor)   { $price = max($price, (float) $rule->min_price_floor); }
        if ($rule->max_price_ceiling) { $price = min($price, (float) $rule->max_price_ceiling); }

        return round($price, 0);
    }
}
