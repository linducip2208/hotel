<?php

namespace App\Services\Accounting;

use App\Models\Pb1Rate;
use App\Models\Property;

class Pb1Calculator
{
    public function rate(Property $property, ?\DateTimeInterface $on = null): float
    {
        $on ??= now();
        $rec = Pb1Rate::query()
            ->where('region_code', $property->region_code)
            ->where('is_active', true)
            ->where('effective_from', '<=', $on)
            ->where(function ($q) use ($on) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>=', $on);
            })
            ->orderByDesc('effective_from')
            ->first();
        return $rec ? (float) $rec->rate : 10.0;
    }

    public function calculate(Property $property, float $base, ?\DateTimeInterface $on = null): float
    {
        return round($base * $this->rate($property, $on) / 100, 2);
    }
}
