<?php

namespace App\Services\Rms;

use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class ForecastAccuracyService
{
    public function calculate(Property $property, int $days = 30): array
    {
        $results = [];
        for ($d = $days; $d >= 1; $d--) {
            $date = now()->subDays($d)->toDateString();

            $forecasted = \App\Models\Inventory::where('property_id', $property->id)
                ->whereDate('date', $date)->sum('forecast_occupancy') ?? 0;

            $actual = Reservation::where('property_id', $property->id)
                ->whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>', $date)
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->count();

            $totalRooms = $property->total_rooms ?: 1;
            $forecastPct = round(($forecasted / $totalRooms) * 100, 1);
            $actualPct = round(($actual / $totalRooms) * 100, 1);
            $error = round($forecastPct - $actualPct, 1);
            $absError = abs($error);

            $results[] = compact('date', 'forecastPct', 'actualPct', 'error', 'absError', 'forecasted', 'actual');
        }

        $count = count($results);
        $avgAbsError = $count > 0 ? round(array_sum(array_column($results, 'absError')) / $count, 1) : 0;
        $mape = $count > 0 ? round(array_sum(array_map(
            fn($r) => $r['actual'] > 0 ? ($r['absError'] / max($r['actual'], 1)) * 100 : 0,
            $results
        )) / $count, 1) : 0;
        $accuracy = round(100 - $avgAbsError, 1);
        $bias = $count > 0 ? round(array_sum(array_column($results, 'error')) / $count, 1) : 0;

        return compact('results', 'avgAbsError', 'mape', 'accuracy', 'bias');
    }
}
