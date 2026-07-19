<?php

namespace App\Services\Promo;

use App\Models\PromoCode;

class PromoService
{
    public function lookup(string $code, int $propertyId): ?PromoCode
    {
        return PromoCode::where('property_id', $propertyId)
            ->where('code', strtoupper($code))
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->first();
    }

    public function apply(PromoCode $promo, float $subtotal): array
    {
        if ($promo->usage_limit && $promo->usage_count >= $promo->usage_limit) {
            return ['ok' => false, 'reason' => 'usage_limit_reached'];
        }

        $discount = match ($promo->discount_type) {
            'pct' => round($subtotal * ($promo->discount_value / 100), 2),
            'amount' => min((float) $promo->discount_value, $subtotal),
            default => 0,
        };

        return [
            'ok' => true,
            'discount' => $discount,
            'subtotal_after' => max(0, $subtotal - $discount),
            'promo_id' => $promo->id,
        ];
    }

    public function recordUsage(PromoCode $promo): void
    {
        $promo->increment('usage_count');
    }
}
