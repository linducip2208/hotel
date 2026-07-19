<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DynamicPricingRule;
use App\Services\Pricing\DynamicPricingService;
use Illuminate\Http\Request;

class DynamicPricingController extends Controller
{
    public function __construct(protected DynamicPricingService $service) {}

    public function rules(Request $request)
    {
        $property = $request->user()->property;
        $rules = DynamicPricingRule::where('property_id', $property->id)
            ->with(['roomType', 'channel'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($rules);
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'room_type_id'      => 'nullable|integer',
            'channel_id'        => 'nullable|integer',
            'trigger_metric'    => 'required|string|in:occupancy_pct,days_to_arrival,pickup_pace,competitor_rate',
            'operator'          => 'required|string|in:gte,lte,between',
            'threshold_low'     => 'required|numeric|min:0',
            'threshold_high'    => 'nullable|numeric|min:0',
            'action'            => 'required|string|in:pct_increase,pct_decrease,fixed_increase,fixed_decrease,stop_sell',
            'action_value'      => 'required|numeric',
            'min_price_floor'   => 'nullable|numeric|min:0',
            'max_price_ceiling' => 'nullable|numeric|min:0',
            'lookahead_days'    => 'nullable|integer|min:1|max:365',
            'is_active'         => 'boolean',
        ]);

        $rule = DynamicPricingRule::create([
            ...$data,
            'property_id' => $request->user()->property->id,
        ]);

        return response()->json($rule, 201);
    }

    public function updateRule(Request $request, int $id)
    {
        $property = $request->user()->property;
        $rule = DynamicPricingRule::where('property_id', $property->id)->findOrFail($id);
        $rule->update($request->only([
            'name', 'trigger_metric', 'operator', 'threshold_low', 'threshold_high',
            'action', 'action_value', 'min_price_floor', 'max_price_ceiling',
            'lookahead_days', 'is_active',
        ]));
        return response()->json($rule);
    }

    public function destroyRule(Request $request, int $id)
    {
        $property = $request->user()->property;
        DynamicPricingRule::where('property_id', $property->id)->findOrFail($id)->delete();
        return response()->noContent();
    }

    public function applyNow(Request $request)
    {
        $property = $request->user()->property;
        $result   = $this->service->applyRules($property);
        return response()->json(['applied' => $result]);
    }

    public function log(Request $request)
    {
        $property = $request->user()->property;
        $logs = \App\Models\DynamicPricingLog::where('property_id', $property->id)
            ->with(['rule', 'roomType', 'channel'])
            ->latest()
            ->paginate(50);
        return response()->json($logs);
    }
}
