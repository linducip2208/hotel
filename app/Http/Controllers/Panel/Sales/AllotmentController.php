<?php

namespace App\Http\Controllers\Panel\Sales;

use App\Http\Controllers\Controller;
use App\Models\Allotment;
use Illuminate\Http\Request;

class AllotmentController extends Controller
{
    public function index()
    {
        $allotments = Allotment::where('property_id', app('current_property')->id)
            ->with('travelAgent', 'company', 'roomType')->latest('from_date')->paginate(50);
        return view('panel.sales.allotments', compact('allotments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travel_agent_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'room_type_id' => 'required|integer',
            'rate_plan_id' => 'nullable|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'rooms_blocked' => 'required|integer|min:1',
            'release_date' => 'nullable|date',
            'negotiated_rate' => 'nullable|numeric',
        ]);
        Allotment::create($data + ['property_id' => app('current_property')->id, 'status' => 'active']);
        return back();
    }

    public function show($id)
    {
        $allotment = Allotment::where('property_id', app('current_property')->id)
            ->with(['travelAgent', 'company', 'roomType', 'ratePlan'])
            ->findOrFail($id);
        return view('panel.sales.allotments-show', compact('allotment'));
    }

    public function update(Request $request, $id)
    {
        $allotment = Allotment::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'travel_agent_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'room_type_id' => 'required|integer',
            'rate_plan_id' => 'nullable|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'rooms_blocked' => 'required|integer|min:1',
            'release_date' => 'nullable|date',
            'negotiated_rate' => 'nullable|numeric',
        ]);
        $allotment->update($data);
        return back()->with('success', 'Allotment berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $allotment = Allotment::where('property_id', app('current_property')->id)->findOrFail($id);
        $allotment->delete();
        return back()->with('success', 'Allotment berhasil dihapus.');
    }

    public function release($id)
    {
        $allotment = Allotment::where('property_id', app('current_property')->id)->findOrFail($id);
        $allotment->update(['status' => 'released']);
        return back()->with('success', 'Allotment telah di-release.');
    }
}
