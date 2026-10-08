<?php

namespace App\Services\Fo;

use App\Models\PromoCode;
use App\Models\Property;
use App\Models\Rate;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Services\Accounting\Pb1Calculator;
use App\Services\Promo\PromoService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for booking pricing.
 *
 * Used by BOTH the public booking engine (search/checkout display) and
 * ReservationService (commit) so the guest can never see a different price
 * than what the reservation records.
 *
 * Fallback policy: when no daily rates exist for a rate plan on a given date,
 * the room type's base_rate is used for that night. This keeps every surface
 * consistent instead of one screen using base_rate and another using Rp 0.
 */
class PricingService
{
    /** Default service charge % applied on room + addons (configurable per property via meta). */
    public const DEFAULT_SERVICE_CHARGE_PCT = 10.0;

    public function __construct(
        protected Pb1Calculator $pb1,
    ) {}

    /**
     * Build a complete price quote for a stay.
     *
     * @return array{
     *     nights: int,
     *     room_type_id: int,
     *     rate_plan_id: ?int,
     *     nightly: array<int, array{date: string, amount: float, from_fallback: bool}>,
     *     room_total: float,
     *     discount: float,
     *     promo_id: ?int,
     *     service_charge_pct: float,
     *     service_charge: float,
     *     tax: float,
     *     grand_total: float,
     *     currency: string,
     *     restrictions: array<int, string>,
     * }
     */
    public function quote(
        int|Property $property,
        int $roomTypeId,
        ?int $ratePlanId,
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults = 1,
        int $children = 0,
        ?PromoCode $promo = null,
    ): array {
        $property = $property instanceof Property ? $property : Property::findOrFail($property);
        $roomType = RoomType::where('property_id', $property->id)->findOrFail($roomTypeId);
        $nights = max(1, (int) $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay()));

        $ratePlan = null;
        if ($ratePlanId) {
            $ratePlan = RatePlan::where('property_id', $property->id)->where('is_active', true)->find($ratePlanId);
        }

        $rates = $this->dailyRates($property->id, $roomTypeId, $ratePlan?->id, $checkIn, $checkOut);
        $restrictions = $this->restrictionIssues($rates, $checkIn, $checkOut, $nights);

        $nightly = [];
        $roomTotal = 0.0;
        $cursor = $checkIn->copy()->startOfDay();
        while ($cursor->lt($checkOut->copy()->startOfDay())) {
            $rate = $rates->get($cursor->toDateString());
            $nightly[] = [
                'date' => $cursor->toDateString(),
                'amount' => $rate ? (float) $rate->amount : (float) $roomType->base_rate,
                'from_fallback' => $rate === null,
            ];
            $roomTotal += $rate ? (float) $rate->amount : (float) $roomType->base_rate;
            $cursor->addDay();
        }

        // Promo discount applies to the room subtotal BEFORE service charge and tax.
        $discount = 0.0;
        $promoId = null;
        if ($promo) {
            $applied = app(PromoService::class)->apply($promo, $roomTotal);
            if ($applied['ok']) {
                $discount = (float) $applied['discount'];
                $promoId = $promo->id;
            }
        }
        $discountedRoomTotal = max(0.0, $roomTotal - $discount);

        $servicePct = $this->serviceChargePct($property);
        $serviceCharge = round($discountedRoomTotal * $servicePct / 100, 2);
        $taxableBase = $discountedRoomTotal + $serviceCharge;
        $tax = $this->pb1->calculate($property, $taxableBase);

        return [
            'nights' => $nights,
            'room_type_id' => $roomType->id,
            'rate_plan_id' => $ratePlan?->id,
            'nightly' => $nightly,
            'room_total' => round($roomTotal, 2),
            'discount' => round($discount, 2),
            'promo_id' => $promoId,
            'service_charge_pct' => $servicePct,
            'service_charge' => $serviceCharge,
            'tax' => $tax,
            'grand_total' => round($taxableBase + $tax, 2),
            'currency' => 'IDR',
            'restrictions' => $restrictions,
        ];
    }

    /**
     * Bookable rate plans for a room type with their total price for a stay.
     *
     * @return Collection<int, array{id: int, code: string, name: string, description: ?string,
     *   is_refundable: bool, breakfast_included: bool, total: float, nightly_min: float, sellable: bool, block_reason: ?string}>
     */
    public function ratePlansForStay(Property|int $property, int $roomTypeId, Carbon $checkIn, Carbon $checkOut, int $nights): Collection
    {
        $property = $property instanceof Property ? $property : Property::findOrFail($property);
        $roomType = RoomType::where('property_id', $property->id)->findOrFail($roomTypeId);

        $plans = RatePlan::where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return $plans->map(function (RatePlan $plan) use ($roomType, $checkIn, $checkOut, $nights, $property) {
            $rates = $this->dailyRates($property->id, $roomType->id, $plan->id, $checkIn, $checkOut);
            $total = 0.0;
            $min = null;
            $cursor = $checkIn->copy()->startOfDay();
            $blockedReason = null;

            while ($cursor->lt($checkOut->copy()->startOfDay())) {
                $rate = $rates->get($cursor->toDateString());
                if ($rate && $rate->closed) {
                    $blockedReason = 'Tanggal tertutup untuk penjualan';
                }
                if ($rate && $rate->cta && $cursor->isSameDay($checkIn)) {
                    $blockedReason = $blockedReason ?? 'Closed to arrival pada tanggal ini';
                }
                if ($rate && $rate->ctd && $cursor->copy()->addDay()->isSameDay($checkOut)) {
                    $blockedReason = $blockedReason ?? 'Closed to departure pada tanggal ini';
                }
                if ($rate && $rate->min_los > $nights) {
                    $blockedReason = $blockedReason ?? 'Minimal menginap '.$rate->min_los.' malam';
                }
                $total += $rate ? (float) $rate->amount : (float) $roomType->base_rate;
                $min = $min === null ? ($rate ? (float) $rate->amount : (float) $roomType->base_rate) : min($min, $rate ? (float) $rate->amount : (float) $roomType->base_rate);
                $cursor->addDay();
            }

            return [
                'id' => $plan->id,
                'code' => $plan->code,
                'name' => $plan->name,
                'description' => $plan->description,
                'is_refundable' => (bool) $plan->is_refundable,
                'breakfast_included' => (bool) $plan->breakfast_included,
                'total' => round($total, 2),
                'nightly_min' => round((float) $min, 2),
                'sellable' => $blockedReason === null,
                'block_reason' => $blockedReason,
            ];
        })->values();
    }

    /**
     * Resolve the default (BAR) rate plan for a property — used by walk-in flow
     * where the operator does not pick a plan. Never a hardcoded ID.
     */
    public function defaultRatePlanId(int $propertyId): ?int
    {
        return RatePlan::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');
    }

    /**
     * Daily rates keyed by date for a rate plan. Returns only rates that exist.
     */
    protected function dailyRates(int $propertyId, int $roomTypeId, ?int $ratePlanId, Carbon $checkIn, Carbon $checkOut): Collection
    {
        $query = Rate::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->whereBetween('date', [$checkIn->copy()->startOfDay()->toDateString(), $checkOut->copy()->startOfDay()->subDay()->toDateString()]);

        if ($ratePlanId) {
            $query->where('rate_plan_id', $ratePlanId);
        } else {
            // No plan chosen: use the cheapest active plan per night.
            $defaultPlanId = $this->defaultRatePlanId($propertyId);
            $query->where('rate_plan_id', $defaultPlanId ?? 0);
        }

        return $query->orderBy('date')->get()->keyBy(fn (Rate $r) => $r->date->toDateString());
    }

    /** @return string[] human-readable restriction issues */
    protected function restrictionIssues(Collection $rates, Carbon $checkIn, Carbon $checkOut, int $nights): array
    {
        $issues = [];
        foreach ($rates as $rate) {
            if ($rate->closed) {
                $issues[] = "{$rate->date->toDateString()}: closed";
            }
            if ($rate->cta && $rate->date->isSameDay($checkIn)) {
                $issues[] = "{$rate->date->toDateString()}: closed to arrival";
            }
            if ($rate->ctd && $rate->date->copy()->addDay()->isSameDay($checkOut)) {
                $issues[] = "{$rate->date->toDateString()}: closed to departure";
            }
            if ($rate->min_los > $nights) {
                $issues[] = "min stay {$rate->min_los} nights";
            }
            if ($rate->max_los && $nights > $rate->max_los) {
                $issues[] = "max stay {$rate->max_los} nights";
            }
        }

        return array_values(array_unique($issues));
    }

    protected function serviceChargePct(Property $property): float
    {
        $settings = $property->settings;
        if (is_array($settings) && isset($settings['service_charge_pct'])) {
            return max(0.0, min(100.0, (float) $settings['service_charge_pct']));
        }

        return self::DEFAULT_SERVICE_CHARGE_PCT;
    }
}
