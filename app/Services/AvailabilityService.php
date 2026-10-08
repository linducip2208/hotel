<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Rate;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Build a month calendar of per-day availability and lowest rate for a property.
     *
     * @return array{year:int, month:int, firstDay:int, daysInMonth:int, days:array<string,array{date:Carbon, available:int, sold_out:bool, lowest_rate:float|null, in_past:bool}>}
     */
    public function calendar(?Property $property = null, ?string $month = null): array
    {
        $property ??= app()->bound('current_property') ? app('current_property') : Property::orderBy('id')->first();

        $base = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $year = (int) $base->format('Y');
        $monthNum = (int) $base->format('m');
        $daysInMonth = (int) $base->daysInMonth;
        $firstDay = (int) $base->copy()->firstOfMonth()->dayOfWeek;

        $start = $base->copy()->startOfMonth();
        $end = $base->copy()->endOfMonth();

        $inventory = $property
            ? Inventory::where('property_id', $property->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get()
            : collect();

        $lowestRates = Rate::query()
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('closed', false)
            ->selectRaw('date, MIN(amount) as min_rate')
            ->groupBy('date')
            ->pluck('min_rate', 'date');

        $days = [];
        $today = now()->toDateString();

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $base->copy()->day($d);
            $key = $date->toDateString();

            $invRows = $inventory->filter(fn ($i) => $i->date->toDateString() === $key);
            $available = $invRows->sum(fn ($i) => $i->available);
            $hasInventory = $invRows->isNotEmpty();

            $days[$key] = [
                'date' => $date,
                'available' => $available,
                'sold_out' => $hasInventory && $available <= 0,
                'no_data' => ! $hasInventory,
                'lowest_rate' => isset($lowestRates[$key]) ? (float) $lowestRates[$key] : null,
                'in_past' => $key < $today,
            ];
        }

        return [
            'year' => $year,
            'month' => $monthNum,
            'firstDay' => $firstDay,
            'daysInMonth' => $daysInMonth,
            'days' => $days,
            'prev' => $base->copy()->subMonth()->format('Y-m'),
            'next' => $base->copy()->addMonth()->format('Y-m'),
        ];
    }
}
