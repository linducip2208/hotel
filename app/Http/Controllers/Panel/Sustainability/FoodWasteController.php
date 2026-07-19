<?php

namespace App\Http\Controllers\Panel\Sustainability;

use App\Http\Controllers\Controller;
use App\Models\FoodWasteLog;
use App\Models\FoodWasteTarget;
use App\Models\PosOutlet;
use App\Services\Sustainability\FoodWasteService;
use Illuminate\Http\Request;

class FoodWasteController extends Controller
{
    public function __construct(protected FoodWasteService $svc) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $propertyId = $property->id;

        $stats = $this->svc->getStats($property);
        $trend = $this->svc->getTrend($property, 30);

        $logs = FoodWasteLog::where('property_id', $propertyId)
            ->with(['outlet', 'loggedBy'])
            ->orderByDesc('logged_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        $outlets = PosOutlet::where('property_id', $propertyId)->where('is_active', true)->get();

        $targets = FoodWasteTarget::where('property_id', $propertyId)
            ->orderByDesc('period_start')
            ->limit(5)
            ->get();

        return view('panel.sustainability.food-waste', array_merge($stats, [
            'trend' => $trend,
            'logs' => $logs,
            'outlets' => $outlets,
            'targets' => $targets,
        ]));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'outlet_id' => 'nullable|exists:pos_outlets,id',
            'waste_category' => 'required|in:prep,spoilage,plate_return,overproduction,expired',
            'food_name' => 'required|string|max:100',
            'quantity_kg' => 'required|numeric|min:0.001',
            'estimated_cost' => 'nullable|numeric|min:0',
            'logged_date' => 'nullable|date',
            'meal_period' => 'required|in:breakfast,lunch,dinner,snack',
            'notes' => 'nullable|string',
        ]);

        $this->svc->logWaste(app('current_property'), $data);
        $this->svc->updateTargetActuals(app('current_property'));

        return back()->with('success', 'Food waste berhasil dicatat.');
    }

    public function storeTarget(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'target_reduction_pct' => 'required|numeric|min:1|max:100',
            'baseline_kg' => 'required|numeric|min:0.001',
        ]);

        FoodWasteTarget::create($data + [
            'property_id' => app('current_property')->id,
            'status' => 'active',
        ]);

        return back()->with('success', 'Target food waste berhasil disimpan.');
    }

    public function completeTarget($id)
    {
        $target = FoodWasteTarget::where('property_id', app('current_property')->id)->findOrFail($id);
        $target->update(['status' => 'completed']);
        return back()->with('success', 'Target ditandai selesai.');
    }
}
