<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\CancellationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CancellationPolicyController extends Controller
{
    public function index()
    {
        $policies = CancellationPolicy::where('property_id', app('current_property')->id)->get();
        return view('panel.settings.cancellation-policies', compact('policies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'is_refundable' => 'nullable|boolean',
            'rules' => 'required|array|min:1',
            'rules.*.days_before' => 'required|integer|min:0',
            'rules.*.penalty_pct' => 'required|numeric|min:0|max:100',
            'display_text' => 'nullable|string',
        ]);
        CancellationPolicy::create($data + [
            'property_id' => app('current_property')->id,
            'code' => Str::slug($data['name']).'-'.Str::random(4),
            'is_active' => true,
        ]);
        return back();
    }
}
