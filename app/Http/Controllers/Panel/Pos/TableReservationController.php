<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\PosOutlet;
use App\Models\RestaurantTable;
use App\Models\TableReservation;
use App\Services\Pos\TableReservationService;
use Illuminate\Http\Request;

class TableReservationController extends Controller
{
    public function __construct(protected TableReservationService $svc) {}

    public function floorplan(Request $request)
    {
        $propertyId = app('current_property')->id;
        $date = $request->query('date', now()->toDateString());
        $outletId = $request->query('outlet_id');

        $plan = $this->svc->getFloorPlan(app('current_property'), $date, $outletId);

        $outlets = PosOutlet::where('property_id', $propertyId)->where('is_active', true)->get();
        $sections = collect($plan)->pluck('table.section')->unique()->filter()->values();

        return view('panel.pos.table-floorplan', [
            'plan' => $plan,
            'date' => $date,
            'outlets' => $outlets,
            'selectedOutletId' => $outletId,
            'sections' => $sections,
        ]);
    }

    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;
        $reservations = TableReservation::where('property_id', $propertyId)
            ->with(['restaurantTable', 'guest', 'bookedBy'])
            ->orderByDesc('reservation_date')
            ->orderByDesc('start_time')
            ->paginate(25);

        $tables = RestaurantTable::where('property_id', $propertyId)->where('is_active', true)->orderBy('table_number')->get();
        $guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->limit(100)->get();
        $outlets = PosOutlet::where('property_id', $propertyId)->where('is_active', true)->get();

        return view('panel.pos.table-reservations', compact('reservations', 'tables', 'guests', 'outlets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_table_id' => 'required|exists:restaurant_tables,id',
            'guest_id' => 'nullable|exists:guests,id',
            'guest_name' => 'required|string|max:100',
            'guest_phone' => 'nullable|string|max:30',
            'party_size' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:30',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['booked_by_user_id'] = auth()->id();

        $this->svc->reserve(app('current_property'), $data);
        return back()->with('success', 'Reservasi meja berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $r = TableReservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'restaurant_table_id' => 'required|exists:restaurant_tables,id',
            'guest_name' => 'required|string|max:100',
            'guest_phone' => 'nullable|string|max:30',
            'party_size' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:30',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $r->update($data);
        return back()->with('success', 'Reservasi meja berhasil diperbarui.');
    }

    public function checkIn($id)
    {
        $r = TableReservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->checkIn($r->id);
        return back()->with('success', 'Tamu sudah duduk.');
    }

    public function complete($id)
    {
        $r = TableReservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->complete($r->id);
        return back()->with('success', 'Reservasi selesai.');
    }

    public function noShow($id)
    {
        $r = TableReservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->noShow($r->id);
        return back()->with('success', 'Ditandai no-show.');
    }

    public function cancel($id)
    {
        $r = TableReservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->cancel($r->id);
        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
