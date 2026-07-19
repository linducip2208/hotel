<?php

namespace App\Http\Controllers\Panel\Sales;

use App\Http\Controllers\Controller;
use App\Models\TravelAgent;
use Illuminate\Http\Request;

class TravelAgentController extends Controller
{
    public function index()
    {
        $agents = TravelAgent::where('property_id', app('current_property')->id)
            ->withCount(['reservations', 'allotments'])
            ->orderBy('name')->paginate(25);
        return view('panel.sales.travel-agents.index', compact('agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'iata_code' => 'nullable|string|max:20',
            'default_commission_pct' => 'nullable|numeric|min:0|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        TravelAgent::create($data + ['property_id' => app('current_property')->id]);
        return back()->with('success', 'Travel agent berhasil ditambahkan.');
    }

    public function show($id)
    {
        $agent = TravelAgent::where('property_id', app('current_property')->id)
            ->with(['allotments.roomType', 'reservations' => fn($q) => $q->latest()->limit(20)])
            ->findOrFail($id);
        return view('panel.sales.travel-agents.show', compact('agent'));
    }

    public function update(Request $request, $id)
    {
        $agent = TravelAgent::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'iata_code' => 'nullable|string|max:20',
            'default_commission_pct' => 'nullable|numeric|min:0|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $agent->update($data);
        return back()->with('success', 'Travel agent berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $agent = TravelAgent::where('property_id', app('current_property')->id)->findOrFail($id);
        $agent->delete();
        return back()->with('success', 'Travel agent berhasil dihapus.');
    }
}
