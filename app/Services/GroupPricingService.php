<?php

namespace App\Services;

use App\Models\RateGroupDiscount;

class GroupPricingService
{
    /**
     * Return the best (largest) applicable discount for a room type / rate plan.
     * Group discounts apply when: active, within date window, matches room type
     * (or global), matches rate plan (or global), and min_nights is satisfied.
     */
    public function bestDiscount(?int $roomTypeId, ?int $ratePlanId, int $nights = 1): ?RateGroupDiscount
    {
        $now = now();

        return RateGroupDiscount::query()
            ->where('is_active', true)
            ->where('min_nights', '<=', max(1, $nights))
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) use ($roomTypeId) {
                $q->whereNull('room_type_id')->orWhere('room_type_id', $roomTypeId);
            })
            ->where(function ($q) use ($ratePlanId) {
                $q->whereNull('rate_plan_id')->orWhere('rate_plan_id', $ratePlanId);
            })
            ->get()
            ->sortByDesc(function (RateGroupDiscount $d) {
                // Rank by discount size in IDR-equivalent terms (roughly).
                return (float) $d->discount_value;
            })
            ->first();
    }

    /**
     * Apply the best applicable group discount to an amount.
     */
    public function apply(float $amount, ?int $roomTypeId, ?int $ratePlanId, int $nights = 1): float
    {
        $discount = $this->bestDiscount($roomTypeId, $ratePlanId, $nights);

        return $discount ? $discount->applyTo($amount) : $amount;
    }
}
