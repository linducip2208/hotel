<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\ParkingRecord;
use App\Models\ParkingSlot;
use App\Models\Reservation;
use App\Services\Fo\ParkingService;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    public function index(ParkingService $service)
    {
        $propertyId = app('current_property')->id;
        $data = $service->getSlots(app('current_property'));
        $activeRecords = ParkingRecord::where('property_id', $propertyId)
            ->where('status', 'parked')
            ->with(['parkingSlot', 'guest', 'reservation'])
            ->orderByDesc('check_in')
            ->get();
        $guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->limit(100)->get();
        $reservations = Reservation::where('property_id', $propertyId)
            ->where('status', 'checked_in')
            ->with('primaryGuest')
            ->orderBy('check_in')
            ->limit(50)
            ->get();

        return view('panel.fo.parking', array_merge($data, [
            'activeRecords' => $activeRecords,
            'guests' => $guests,
            'reservations' => $reservations,
        ]));
    }

    public function checkIn(Request $request, ParkingService $service)
    {
        $data = $request->validate([
            'parking_slot_id' => 'required|exists:parking_slots,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'vehicle_plate' => 'required|string|max:20',
            'vehicle_type' => 'nullable|in:car,motorcycle,bus,truck',
            'vehicle_brand' => 'nullable|string|max:50',
            'vehicle_color' => 'nullable|string|max:30',
            'daily_rate' => 'nullable|numeric|min:0',
            'is_valet' => 'boolean',
            'valet_key_location' => 'nullable|string|max:100',
        ]);

        $data['valet_by_user_id'] = $data['is_valet'] ?? false ? auth()->id() : null;

        $service->checkIn(app('current_property'), $data);

        return back()->with('success', 'Kendaraan berhasil check-in.');
    }

    public function checkOut($id, Request $request, ParkingService $service)
    {
        $record = ParkingRecord::where('property_id', app('current_property')->id)->findOrFail($id);
        $service->checkOut($record->id);

        if ($request->has('folio_id') && $record->total_charge > 0) {
            $folio = \App\Models\Folio::where('property_id', app('current_property')->id)
                ->find($request->folio_id);
            if ($folio) {
                $service->chargeToFolio($record, $folio);
                return back()->with('success', 'Kendaraan keluar dan biaya dibebankan ke folio.');
            }
        }

        return back()->with('success', 'Kendaraan berhasil check-out.');
    }

    public function valet(ParkingService $service)
    {
        $valetRecords = ParkingRecord::where('property_id', app('current_property')->id)
            ->where('is_valet', true)
            ->where('status', 'parked')
            ->with(['parkingSlot', 'guest', 'valetByUser'])
            ->orderByDesc('check_in')
            ->get();

        return view('panel.fo.parking-valet', compact('valetRecords'));
    }
}
