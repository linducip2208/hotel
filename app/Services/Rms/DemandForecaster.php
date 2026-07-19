<?php

namespace App\Services\Rms;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class DemandForecaster
{
    /**
     * Simple rule-based forecast: same period last year + recent pickup pace.
     * Output: per-date predicted occupancy, suggested rate adjust pct.
     */
    public function forecast(Property $property, Carbon $from, Carbon $to): array
    {
        $result = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $lastYear = $cursor->copy()->subYear();
            $lyOccupancy = $this->occupancyAt($property, $lastYear);
            $currentBooked = $this->bookedAt($property, $cursor);
            $totalRooms = max(1, $property->total_rooms);
            $expectedOcc = min(100, max($lyOccupancy, ($currentBooked / $totalRooms) * 100 + 15));

            $result[] = [
                'date' => $cursor->toDateString(),
                'last_year_occupancy_pct' => $lyOccupancy,
                'current_booked' => $currentBooked,
                'total_rooms' => $totalRooms,
                'forecast_occupancy_pct' => round($expectedOcc, 2),
                'suggested_rate_modifier_pct' => $this->suggestRateModifier($expectedOcc),
            ];
            $cursor->addDay();
        }
        return $result;
    }

    protected function occupancyAt(Property $property, Carbon $date): float
    {
        $inv = Inventory::where('property_id', $property->id)
            ->whereDate('date', $date->toDateString())->sum('sold');
        return $property->total_rooms > 0
            ? round(($inv / $property->total_rooms) * 100, 2)
            : 0;
    }

    protected function bookedAt(Property $property, Carbon $date): int
    {
        return (int) Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();
    }

    protected function suggestRateModifier(float $expectedOcc): float
    {
        if ($expectedOcc >= 90) return 25;
        if ($expectedOcc >= 80) return 15;
        if ($expectedOcc >= 70) return 8;
        if ($expectedOcc >= 50) return 0;
        if ($expectedOcc >= 30) return -10;
        return -20;
    }
}
