<?php

namespace App\Http\Controllers\Panel\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\WorkOrder;
use App\Services\Maintenance\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.asset.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'room_id' => 'nullable|integer',
            'purchased_at' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'useful_life_years' => 'nullable|integer',
        ]);
        Asset::create($data + [
            'property_id' => app('current_property')->id,
            'asset_no' => 'AST-'.now()->format('Y').'-'.Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        return back();
    }

    public function show(int $id)
    {
        $asset = Asset::where('property_id', app('current_property')->id)->with('workOrders', 'ppmSchedules')->findOrFail($id);
        return view('panel.asset.show', compact('asset'));
    }

    public function workOrders(Request $request)
    {
        $orders = WorkOrder::where('property_id', app('current_property')->id)
            ->with('asset', 'room', 'assignee')->orderByDesc('reported_at')->paginate(50);
        return view('panel.asset.work-orders', compact('orders'));
    }

    public function storeWorkOrder(Request $request, WorkOrderService $svc)
    {
        $data = $request->validate([
            'asset_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
            'type' => 'required|in:corrective,preventive,inspection',
            'priority' => 'nullable|string',
            'description' => 'required|string',
            'assignee_id' => 'nullable|integer',
        ]);
        $svc->create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function updateWorkOrder(Request $request, int $id, WorkOrderService $svc)
    {
        $wo = WorkOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $action = $request->input('action');
        match ($action) {
            'start' => $svc->start($wo, $request->user()?->id),
            'complete' => $svc->complete($wo, $request->input('resolution')),
            'verify' => $svc->verify($wo),
            default => null,
        };
        return back();
    }

    public function ppm()
    {
        $schedules = PreventiveMaintenanceSchedule::where('property_id', app('current_property')->id)
            ->with('asset')->orderBy('next_due_at')->paginate(50);
        return view('panel.asset.ppm', compact('schedules'));
    }
}
