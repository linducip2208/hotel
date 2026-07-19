<?php

namespace App\Http\Controllers\Panel\Pricing;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelParityAlert;
use App\Models\DynamicPricingRule;
use App\Models\RoomType;
use App\Services\Channel\ParityMonitorService;
use App\Services\Pricing\DynamicPricingService;
use App\Services\Pricing\OpenPricingService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __construct(
        protected OpenPricingService    $openPricing,
        protected DynamicPricingService $dynPricing,
        protected ParityMonitorService  $parity,
    ) {}

    // ── Open Pricing calendar ────────────────────────────────────────────
    public function calendar(Request $request)
    {
        $property  = $request->user()->property;
        $roomTypes = RoomType::where('property_id', $property->id)->get();
        $channels  = Channel::where('property_id', $property->id)->where('is_active', true)->get();
        return view('panel.pricing.calendar', compact('property', 'roomTypes', 'channels'));
    }

    public function calendarData(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|integer',
            'channel_id'   => 'nullable|integer',
            'from'         => 'required|date_format:Y-m-d',
            'to'           => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);
        $property = $request->user()->property;
        $roomType = RoomType::where('property_id', $property->id)->findOrFail($request->integer('room_type_id'));
        $channel  = $request->filled('channel_id')
            ? Channel::where('property_id', $property->id)->findOrFail($request->integer('channel_id'))
            : null;
        $grid = $this->openPricing->availabilityGrid($property, $roomType, $channel, $request->from, $request->to);
        return response()->json($grid);
    }

    public function bulkSave(Request $request)
    {
        $request->validate([
            'overrides'                  => 'required|array|min:1|max:365',
            'overrides.*.room_type_id'   => 'required|integer',
            'overrides.*.channel_id'     => 'nullable|integer',
            'overrides.*.date'           => 'required|date_format:Y-m-d',
            'overrides.*.price'          => 'required|numeric|min:0',
            'overrides.*.stop_sell'      => 'nullable|boolean',
            'overrides.*.closed_to_arrival' => 'nullable|boolean',
            'overrides.*.min_stay'       => 'nullable|integer|min:1',
        ]);
        $property = $request->user()->property;
        $count    = $this->openPricing->bulkUpsert($property, $request->input('overrides'));
        return response()->json(['upserted' => $count]);
    }

    // ── Dynamic Pricing rules ────────────────────────────────────────────
    public function rules(Request $request)
    {
        $property  = $request->user()->property;
        $rules     = DynamicPricingRule::where('property_id', $property->id)
            ->with(['roomType', 'channel'])
            ->orderBy('created_at', 'desc')
            ->get();
        $roomTypes = RoomType::where('property_id', $property->id)->get();
        $channels  = Channel::where('property_id', $property->id)->where('is_active', true)->get();
        return view('panel.pricing.rules', compact('property', 'rules', 'roomTypes', 'channels'));
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'room_type_id'      => 'nullable|integer',
            'channel_id'        => 'nullable|integer',
            'trigger_metric'    => 'required|string',
            'operator'          => 'required|string|in:gte,lte,between',
            'threshold_low'     => 'required|numeric|min:0',
            'threshold_high'    => 'nullable|numeric|min:0',
            'action'            => 'required|string',
            'action_value'      => 'required|numeric',
            'min_price_floor'   => 'nullable|numeric|min:0',
            'max_price_ceiling' => 'nullable|numeric|min:0',
            'lookahead_days'    => 'nullable|integer|min:1|max:365',
            'is_active'         => 'boolean',
        ]);
        DynamicPricingRule::create(['property_id' => $request->user()->property->id] + $data);
        return back()->with('success', 'Rule berhasil disimpan.');
    }

    public function applyRulesNow(Request $request)
    {
        $property = $request->user()->property;
        $applied  = $this->dynPricing->applyRules($property);
        return back()->with('success', "{$applied} price override diterapkan.");
    }

    // ── Channel Parity ────────────────────────────────────────────────────
    public function parity(Request $request)
    {
        $property = $request->user()->property;
        $alerts   = ChannelParityAlert::where('property_id', $property->id)
            ->with(['roomType', 'channel'])
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('panel.pricing.parity', compact('property', 'alerts'));
    }

    public function checkParityNow(Request $request)
    {
        $property = $request->user()->property;
        $count    = $this->parity->checkAndAlert($property);
        return back()->with('success', "{$count} parity alert baru dibuat.");
    }

    public function resolveAlert(Request $request, int $id)
    {
        $request->validate(['action' => 'required|string|max:500']);
        $property = $request->user()->property;
        $alert    = ChannelParityAlert::where('property_id', $property->id)->findOrFail($id);
        $this->parity->resolve($alert, $request->user()->id, $request->action, $request->notes);
        return back()->with('success', 'Alert diselesaikan.');
    }

    public function logs(Request $request)
    {
        $logs = \App\Models\DynamicPricingLog::where('property_id', app('current_property')->id)
            ->with(['rule', 'roomType', 'channel'])
            ->latest()->paginate(50);
        return view('panel.pricing.logs', compact('logs'));
    }
}
