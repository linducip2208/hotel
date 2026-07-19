<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\Pb1Rate;
use App\Models\RatePlan;
use Illuminate\Http\Request;

class TaxConfigController extends Controller
{
    public function edit()
    {
        $property = app('current_property');
        $rates = Pb1Rate::where('region_code', $property->region_code)->orderByDesc('effective_from')->get();
        $ratePlans = RatePlan::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();
        return view('panel.settings.tax', compact('property', 'rates', 'ratePlans'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'region_code' => 'required|string',
            'is_pkp' => 'nullable|boolean',
            'npwp' => 'nullable|string',
            'nsfp_series' => 'nullable|string',
        ]);
        app('current_property')->update($data);
        return back()->with('status', 'Tax config updated.');
    }

    public function updateDepositConfig(Request $request)
    {
        $data = $request->validate([
            'rate_plan_id' => 'required|array',
            'rate_plan_id.*' => 'required|integer|exists:rate_plans,id',
            'deposit_type' => 'required|array',
            'deposit_type.*' => 'required|in:none,percentage,fixed,night_count',
            'deposit_value' => 'nullable|array',
            'deposit_value.*' => 'nullable|numeric|min:0',
            'deposit_due_days' => 'nullable|array',
            'deposit_due_days.*' => 'nullable|integer|min:0',
        ]);

        foreach ($data['rate_plan_id'] as $i => $rpId) {
            $ratePlan = RatePlan::findOrFail($rpId);
            $ratePlan->update([
                'deposit_config' => [
                    'type' => $data['deposit_type'][$i] ?? 'none',
                    'value' => isset($data['deposit_value'][$i]) ? (float) $data['deposit_value'][$i] : null,
                    'due_days_before_checkin' => isset($data['deposit_due_days'][$i]) ? (int) $data['deposit_due_days'][$i] : null,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        return back()->with('status', 'Konfigurasi deposit berhasil disimpan.');
    }
}
