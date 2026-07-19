<?php

namespace App\Services\Sustainability;

use App\Models\IotEnergyLog;
use App\Models\Property;
use Carbon\Carbon;

class EnergyService
{
    public function getDashboard(Property $property): array
    {
        $month = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentEnergy = IotEnergyLog::where('property_id', $property->id)
            ->where('log_date', '>=', $month)->sum('energy_kwh');
        $lastMonthEnergy = IotEnergyLog::where('property_id', $property->id)
            ->whereBetween('log_date', [$lastMonth, $month->copy()->subDay()])->sum('energy_kwh');

        $change = $lastMonthEnergy > 0 ? round((($currentEnergy - $lastMonthEnergy) / $lastMonthEnergy) * 100, 1) : 0;

        $dailyAvg = round($currentEnergy / max(now()->day, 1), 2);
        $dailyCost = round($dailyAvg * 1500, 0);

        $carbonKg = round($currentEnergy * 0.85, 0);
        $carbonOffset = round($carbonKg * 0.1, 0);

        $byRoom = IotEnergyLog::where('property_id', $property->id)
            ->where('log_date', '>=', $month)
            ->with('room')
            ->selectRaw('room_id, SUM(energy_kwh) as total_kwh, SUM(cost_estimate) as total_cost')
            ->groupBy('room_id')->orderByDesc('total_kwh')->limit(10)->get();

        $suggestions = [];
        if ($change > 5) {
            $suggestions[] = "Konsumsi naik {$change}% vs bulan lalu. Periksa AC dan water heater.";
        }
        if ($dailyAvg > ($property->total_rooms ?? 100) * 15) {
            $suggestions[] = "Konsumsi per kamar tinggi. Pertimbangkan smart thermostat.";
        }
        if (count($suggestions) === 0) {
            $suggestions[] = "Konsumsi energi dalam batas normal. Pertahankan!";
        }

        return compact(
            'currentEnergy', 'lastMonthEnergy', 'change', 'dailyAvg', 'dailyCost',
            'carbonKg', 'carbonOffset', 'byRoom', 'suggestions'
        );
    }

    public function getAnnualReport(Property $property, int $year): array
    {
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1);
            $end = $start->copy()->endOfMonth();
            $kwh = IotEnergyLog::where('property_id', $property->id)
                ->whereBetween('log_date', [$start, $end])->sum('energy_kwh');
            $cost = IotEnergyLog::where('property_id', $property->id)
                ->whereBetween('log_date', [$start, $end])->sum('cost_estimate');
            $monthly[] = ['month' => $start->format('M'), 'kwh' => round($kwh, 0), 'cost' => round($cost, 0)];
        }

        $totalKwh = (int) array_sum(array_column($monthly, 'kwh'));
        $totalCost = (int) array_sum(array_column($monthly, 'cost'));
        $carbonTons = round($totalKwh * 0.85 / 1000, 2);

        return compact('monthly', 'totalKwh', 'totalCost', 'carbonTons');
    }
}
