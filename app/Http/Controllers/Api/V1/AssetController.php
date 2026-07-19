<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Property;
use App\Models\WorkOrder;
use App\Services\Maintenance\WorkOrderService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function assets()
    {
        return response()->json(
            Asset::where('property_id', $this->property()->id)->paginate(50)
        );
    }

    public function show(int $id)
    {
        return response()->json(
            Asset::where('property_id', $this->property()->id)
                ->with('workOrders')
                ->findOrFail($id)
        );
    }

    public function workOrders(Request $request)
    {
        $query = WorkOrder::where('property_id', $this->property()->id)
            ->with('asset', 'room')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json($query->paginate(50));
    }

    public function createWorkOrder(Request $request, WorkOrderService $svc)
    {
        $validated = $request->validate([
            'asset_id'    => 'nullable|integer|exists:assets,id',
            'room_id'     => 'nullable|integer|exists:rooms,id',
            'type'        => 'required|string|max:50',
            'priority'    => 'nullable|in:low,normal,high,urgent',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:employees,id',
        ]);

        $validated['property_id'] = $this->property()->id;

        return response()->json($svc->create($validated), 201);
    }

    public function updateWorkOrder(Request $request, int $id, WorkOrderService $svc)
    {
        $validated = $request->validate([
            'action'     => 'required|in:start,complete,verify',
            'resolution' => 'nullable|string|max:500',
        ]);

        $wo = WorkOrder::where('property_id', $this->property()->id)->findOrFail($id);

        match ($validated['action']) {
            'start'    => $svc->start($wo, $request->user()?->id),
            'complete' => $svc->complete($wo, $validated['resolution'] ?? null),
            'verify'   => $svc->verify($wo),
        };

        return response()->json($wo->fresh());
    }
}
