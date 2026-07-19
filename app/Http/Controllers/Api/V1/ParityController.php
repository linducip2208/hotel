<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChannelParityAlert;
use App\Services\Channel\ParityMonitorService;
use Illuminate\Http\Request;

class ParityController extends Controller
{
    public function __construct(protected ParityMonitorService $monitor) {}

    public function index(Request $request)
    {
        $property = $request->user()->property;
        $query = ChannelParityAlert::where('property_id', $property->id)
            ->with(['roomType', 'channel'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        return response()->json($query->paginate(25));
    }

    public function checkNow(Request $request)
    {
        $property = $request->user()->property;
        $count    = $this->monitor->checkAndAlert($property);
        return response()->json(['alerts_created' => $count]);
    }

    public function acknowledge(Request $request, int $id)
    {
        $property = $request->user()->property;
        $alert    = ChannelParityAlert::where('property_id', $property->id)->findOrFail($id);
        $this->monitor->acknowledge($alert, $request->user()->id, $request->input('notes'));
        return response()->json($alert->fresh());
    }

    public function resolve(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|string|max:500',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $property = $request->user()->property;
        $alert    = ChannelParityAlert::where('property_id', $property->id)->findOrFail($id);
        $this->monitor->resolve($alert, $request->user()->id, $request->action, $request->notes);
        return response()->json($alert->fresh());
    }
}
