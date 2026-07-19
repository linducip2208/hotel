<?php

namespace App\Services\Pos;

use App\Models\MenuPerformance;
use App\Models\MenuRecipe;
use App\Models\Property;
use Carbon\Carbon;

class MenuEngineeringService
{
    public function classifyMenuItems(Property $property, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): array
    {
        $start = $periodStart ?? Carbon::now()->subDays(30)->startOfDay();
        $end = $periodEnd ?? Carbon::now()->endOfDay();

        $recipes = MenuRecipe::where('property_id', $property->id)
            ->with('ingredients')
            ->get();

        $performances = MenuPerformance::where('property_id', $property->id)
            ->whereBetween('period_start', [$start, $end])
            ->get()
            ->keyBy('menu_recipe_id');

        $totalUnitsSold = $performances->sum('units_sold');
        $totalRevenue = $performances->sum('total_revenue');

        $avgMargin = $recipes->isNotEmpty()
            ? $recipes->avg(fn($r) => $r->selling_price > 0 ? round((($r->selling_price - $r->food_cost) / $r->selling_price) * 100, 2) : 0)
            : 0;

        $avgPopularity = $totalUnitsSold > 0 && $recipes->isNotEmpty()
            ? round(($totalUnitsSold / $recipes->count()) / max($totalUnitsSold, 1) * 100, 2)
            : 0;

        $results = [];
        foreach ($recipes as $recipe) {
            $perf = $performances->get($recipe->id);
            $margin = $recipe->selling_price > 0
                ? round((($recipe->selling_price - $recipe->food_cost) / $recipe->selling_price) * 100, 2)
                : 0;
            $popularity = $totalUnitsSold > 0 && $perf
                ? round(($perf->units_sold / max($totalUnitsSold, 1)) * 100, 2)
                : 0;

            $quadrant = $this->getQuadrant($margin, $popularity, $avgMargin, $avgPopularity);

            $results[] = [
                'recipe' => $recipe,
                'food_cost' => $recipe->food_cost,
                'food_cost_pct' => $recipe->food_cost_pct,
                'margin' => $margin,
                'popularity' => $popularity,
                'quadrant' => $quadrant,
                'units_sold' => $perf->units_sold ?? 0,
                'total_revenue' => $perf->total_revenue ?? 0,
                'total_cost' => $perf->total_cost ?? 0,
                'gross_profit' => $perf->gross_profit ?? ($recipe->gross_profit),
            ];
        }

        return [
            'items' => $results,
            'stars' => array_values(array_filter($results, fn($r) => $r['quadrant'] === 'star')),
            'plowhorses' => array_values(array_filter($results, fn($r) => $r['quadrant'] === 'plowhorse')),
            'puzzles' => array_values(array_filter($results, fn($r) => $r['quadrant'] === 'puzzle')),
            'dogs' => array_values(array_filter($results, fn($r) => $r['quadrant'] === 'dog')),
            'avg_margin' => $avgMargin,
            'avg_popularity' => $avgPopularity,
        ];
    }

    protected function getQuadrant(float $margin, float $popularity, float $avgMargin, float $avgPopularity): string
    {
        $highMargin = $margin >= $avgMargin;
        $highPopularity = $popularity >= $avgPopularity;

        if ($highMargin && $highPopularity) return 'star';
        if (!$highMargin && $highPopularity) return 'plowhorse';
        if ($highMargin && !$highPopularity) return 'puzzle';
        return 'dog';
    }

    public function calculatePerformance(Property $property, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): void
    {
        $start = $periodStart ?? Carbon::now()->subDays(30)->startOfDay();
        $end = $periodEnd ?? Carbon::now()->endOfDay();

        $recipes = MenuRecipe::where('property_id', $property->id)->get();

        $totalUnits = 0;
        foreach ($recipes as $recipe) {
            $unitsSold = $recipe->menuItem
                ? \App\Models\PosOrderItem::whereHas('order', fn($q) => $q->where('property_id', $property->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'settled'))
                    ->where('menu_item_id', $recipe->menu_item_id)
                    ->sum('quantity')
                : 0;
            $totalUnits += $unitsSold;
        }

        foreach ($recipes as $recipe) {
            $unitsSold = $recipe->menuItem
                ? \App\Models\PosOrderItem::whereHas('order', fn($q) => $q->where('property_id', $property->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'settled'))
                    ->where('menu_item_id', $recipe->menu_item_id)
                    ->sum('quantity')
                : 0;

            $totalRevenue = $unitsSold * $recipe->selling_price;
            $totalCost = $unitsSold * $recipe->food_cost;
            $grossProfit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;
            $popularity = $totalUnits > 0 ? round(($unitsSold / $totalUnits) * 100, 2) : 0;

            $existing = MenuPerformance::where('property_id', $property->id)
                ->where('menu_recipe_id', $recipe->id)
                ->where('period_start', $start->toDateString())
                ->where('period_end', $end->toDateString())
                ->first();

            if ($existing) {
                $existing->update([
                    'units_sold' => $unitsSold,
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'gross_profit' => $grossProfit,
                    'profit_margin_pct' => $profitMargin,
                    'popularity_pct' => $popularity,
                ]);
            } else {
                MenuPerformance::create([
                    'property_id' => $property->id,
                    'menu_recipe_id' => $recipe->id,
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'units_sold' => $unitsSold,
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'gross_profit' => $grossProfit,
                    'profit_margin_pct' => $profitMargin,
                    'popularity_pct' => $popularity,
                    'category' => $recipe->category,
                ]);
            }
        }
    }
}
