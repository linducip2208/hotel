<?php

namespace App\Services\Sustainability;

use App\Models\FoodWasteLog;
use App\Models\FoodWasteTarget;
use App\Models\Property;
use Carbon\Carbon;

class FoodWasteService
{
    public function getStats(Property $property): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::today()->startOfWeek();
        $monthStart = Carbon::today()->startOfMonth();

        $todayStats = FoodWasteLog::where('property_id', $property->id)
            ->whereDate('logged_date', $today)
            ->selectRaw('SUM(quantity_kg) as total_kg, SUM(estimated_cost) as total_cost')
            ->first();

        $weekStats = FoodWasteLog::where('property_id', $property->id)
            ->whereBetween('logged_date', [$weekStart, $today])
            ->selectRaw('SUM(quantity_kg) as total_kg, SUM(estimated_cost) as total_cost')
            ->first();

        $monthStats = FoodWasteLog::where('property_id', $property->id)
            ->whereBetween('logged_date', [$monthStart, $today])
            ->selectRaw('SUM(quantity_kg) as total_kg, SUM(estimated_cost) as total_cost')
            ->first();

        $lastMonthStart = Carbon::today()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::today()->subMonth()->endOfMonth();
        $lastMonthStats = FoodWasteLog::where('property_id', $property->id)
            ->whereBetween('logged_date', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw('SUM(quantity_kg) as total_kg')
            ->first();

        $monthChange = $lastMonthStats && $lastMonthStats->total_kg > 0
            ? round((($monthStats->total_kg - $lastMonthStats->total_kg) / $lastMonthStats->total_kg) * 100, 1)
            : 0;

        $byCategory = FoodWasteLog::where('property_id', $property->id)
            ->whereBetween('logged_date', [$monthStart, $today])
            ->selectRaw('waste_category, SUM(quantity_kg) as total_kg, SUM(estimated_cost) as total_cost')
            ->groupBy('waste_category')
            ->get();

        $activeTarget = FoodWasteTarget::where('property_id', $property->id)
            ->where('status', 'active')
            ->first();

        $targetProgress = null;
        if ($activeTarget) {
            $targetProgress = [
                'target' => $activeTarget,
                'achieved_kg' => $activeTarget->actual_kg,
                'baseline_kg' => $activeTarget->baseline_kg,
                'reduction_pct' => $activeTarget->baseline_kg > 0
                    ? round((($activeTarget->baseline_kg - $activeTarget->actual_kg) / $activeTarget->baseline_kg) * 100, 1)
                    : 0,
                'target_pct' => $activeTarget->target_reduction_pct,
            ];
        }

        return [
            'today' => $todayStats,
            'this_week' => $weekStats,
            'this_month' => $monthStats,
            'month_change_pct' => $monthChange,
            'by_category' => $byCategory,
            'target_progress' => $targetProgress,
        ];
    }

    public function logWaste(Property $property, array $data): FoodWasteLog
    {
        return FoodWasteLog::create([
            'property_id' => $property->id,
            'outlet_id' => $data['outlet_id'] ?? null,
            'waste_category' => $data['waste_category'],
            'food_name' => $data['food_name'],
            'quantity_kg' => $data['quantity_kg'],
            'estimated_cost' => $data['estimated_cost'] ?? 0,
            'logged_by_user_id' => auth()->id(),
            'logged_date' => $data['logged_date'] ?? Carbon::today()->toDateString(),
            'meal_period' => $data['meal_period'] ?? 'lunch',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updateTargetActuals(Property $property): void
    {
        $activeTargets = FoodWasteTarget::where('property_id', $property->id)
            ->where('status', 'active')
            ->get();

        foreach ($activeTargets as $target) {
            $actualKg = FoodWasteLog::where('property_id', $property->id)
                ->whereBetween('logged_date', [$target->period_start, min($target->period_end, Carbon::today())])
                ->sum('quantity_kg');

            $target->update(['actual_kg' => $actualKg]);
        }
    }

    public function getTrend(Property $property, int $days = 30): array
    {
        $start = Carbon::today()->subDays($days);

        return FoodWasteLog::where('property_id', $property->id)
            ->where('logged_date', '>=', $start)
            ->selectRaw('logged_date, SUM(quantity_kg) as total_kg, SUM(estimated_cost) as total_cost')
            ->groupBy('logged_date')
            ->orderBy('logged_date')
            ->get()
            ->toArray();
    }
}
