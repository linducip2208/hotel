<?php

namespace App\Http\Controllers\Panel\Sustainability;

use App\Http\Controllers\Controller;
use App\Models\CarbonFootprint;
use App\Models\SustainabilityMetric;
use Illuminate\Http\Request;

class SustainabilityController extends Controller
{
    public function dashboard(Request $request)
    {
        $monthCo2 = (float) CarbonFootprint::where('property_id', app('current_property')->id)
            ->whereMonth('period_date', now()->month)
            ->whereYear('period_date', now()->year)
            ->sum('co2e_kg');
        $metrics = SustainabilityMetric::where('property_id', app('current_property')->id)
            ->orderByDesc('measurement_date')->limit(50)->get();
        return view('panel.sustainability.dashboard', compact('monthCo2', 'metrics'));
    }

    public function storeMetric(Request $request)
    {
        $data = $request->validate([
            'measurement_date' => 'required|date',
            'metric' => 'required|in:energy_kwh,water_m3,waste_kg,recycled_pct,renewable_pct',
            'value' => 'required|numeric',
            'unit' => 'nullable|string',
            'source' => 'nullable|string',
        ]);
        SustainabilityMetric::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }
}
