<?php

namespace App\Services\Pricing;

use App\Models\Channel;
use App\Models\Property;
use App\Models\Rate;
use App\Models\RateOverride;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Open Pricing Engine: returns the effective price for a given
 * room_type × channel × date combination by merging:
 *   1. Explicit RateOverride (highest priority)
 *   2. Rate (base rate from rate_plans)
 *   3. RoomType.base_rate (fallback)
 *
 * Also handles stop_sell, min/max stay, and closed_to_arrival flags.
 */
class OpenPricingService
{
    /**
     * Get effective price for a specific room type / channel / date.
     */
    public function effectivePrice(
        int $propertyId,
        int $roomTypeId,
        ?int $channelId,
        string $date
    ): array {
        // Check for channel-specific override first, then property-wide override
        $override = RateOverride::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('override_date', $date)
            ->where(function ($q) use ($channelId) {
                $q->where('channel_id', $channelId)
                  ->orWhereNull('channel_id');
            })
            ->orderByRaw('channel_id IS NULL ASC') // channel-specific wins
            ->first();

        if ($override) {
            return [
                'price'              => (float) $override->price,
                'source'             => 'override',
                'stop_sell'          => $override->stop_sell,
                'closed_to_arrival'  => $override->closed_to_arrival,
                'min_stay'           => $override->min_stay,
                'override_id'        => $override->id,
            ];
        }

        // Fall back to base rate from rate_plans
        $rate = Rate::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('date', $date)
            ->first();

        if ($rate) {
            return [
                'price'             => (float) $rate->price,
                'source'            => 'rate',
                'stop_sell'         => false,
                'closed_to_arrival' => false,
                'min_stay'          => 1,
            ];
        }

        // Final fallback: room type base_rate
        $rt = RoomType::find($roomTypeId);
        return [
            'price'             => $rt ? (float) $rt->base_rate : 0,
            'source'            => 'base_rate',
            'stop_sell'         => false,
            'closed_to_arrival' => false,
            'min_stay'          => 1,
        ];
    }

    /**
     * Bulk upsert rate overrides — used by dynamic pricing and import.
     */
    public function bulkUpsert(Property $property, array $overrides): int
    {
        $count = 0;
        foreach ($overrides as $item) {
            RateOverride::updateOrCreate(
                [
                    'property_id'   => $property->id,
                    'room_type_id'  => $item['room_type_id'],
                    'channel_id'    => $item['channel_id'] ?? null,
                    'override_date' => $item['date'],
                ],
                [
                    'price'               => $item['price'],
                    'min_price'           => $item['min_price'] ?? null,
                    'max_price'           => $item['max_price'] ?? null,
                    'min_stay'            => $item['min_stay'] ?? 1,
                    'closed_to_arrival'   => $item['closed_to_arrival'] ?? false,
                    'stop_sell'           => $item['stop_sell'] ?? false,
                    'source'              => $item['source'] ?? 'manual',
                    'created_by_user_id'  => $item['user_id'] ?? null,
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Get availability grid: price + availability for each date in range.
     */
    public function availabilityGrid(
        Property $property,
        RoomType $roomType,
        ?Channel $channel,
        string $fromDate,
        string $toDate
    ): Collection {
        $dates = collect();
        $cursor = Carbon::parse($fromDate);
        $end    = Carbon::parse($toDate);

        while ($cursor->lte($end)) {
            $ds      = $cursor->toDateString();
            $pricing = $this->effectivePrice($property->id, $roomType->id, $channel?->id, $ds);

            $dates->push([
                'date'             => $ds,
                'price'            => $pricing['price'],
                'source'           => $pricing['source'],
                'stop_sell'        => $pricing['stop_sell'],
                'closed_to_arrival'=> $pricing['closed_to_arrival'],
                'min_stay'         => $pricing['min_stay'],
            ]);

            $cursor->addDay();
        }

        return $dates;
    }
}
