<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MicrostayRate;
use App\Services\Fo\MicrostayService;
use Illuminate\Http\Request;

class MicrostayController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $rates = MicrostayRate::where('property_id', $property->id)
            ->with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('hours')
            ->get();

        return view('panel.microstay.index', compact('property', 'rates'));
    }

    public function rates(Request $request)
    {
        $property = app('current_property');
        $rates = MicrostayRate::where('property_id', $property->id)
            ->with('roomType')
            ->get();

        $roomTypes = \App\Models\RoomType::where('property_id', $property->id)
            ->where('is_active', true)
            ->get();

        return view('panel.microstay.rates', compact('property', 'rates', 'roomTypes'));
    }

    public function storeRate(Request $request)
    {
        $property = app('current_property');
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'hours' => 'required|integer|in:3,6,12',
            'price' => 'required|numeric|min:0',
            'earliest_checkin' => 'required|date_format:H:i',
            'latest_checkin' => 'required|date_format:H:i',
        ]);

        MicrostayRate::create(array_merge($validated, [
            'property_id' => $property->id,
            'is_active' => true,
        ]));

        return back()->with('success', 'Microstay rate berhasil ditambahkan.');
    }

    public function updateRate(Request $request, $id)
    {
        $rate = MicrostayRate::findOrFail($id);
        $rate->update($request->validate([
            'price' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]));

        return back()->with('success', 'Rate diupdate.');
    }

    public function destroyRate($id)
    {
        MicrostayRate::findOrFail($id)->delete();
        return back()->with('success', 'Rate dihapus.');
    }

    public function book(Request $request, MicrostayService $service)
    {
        $property = app('current_property');
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'hours' => 'required|integer|in:3,6,12',
            'check_in' => 'required|date',
            'adults' => 'nullable|integer|min:1|max:4',
        ]);

        $price = $service->calculatePrice($property->id, $validated['room_type_id'], (int)$validated['hours']);
        if (!$price) return back()->with('error', 'Tidak bisa menghitung harga.');

        $reservation = $service->createMicrostayReservation([
            'property_id' => $property->id,
            'check_in' => $validated['check_in'],
            'microstay_hours' => (int)$validated['hours'],
            'total_room' => $price,
            'adults' => $validated['adults'] ?? 1,
        ]);

        return redirect()->route('panel.fo.reservations.show', $reservation->id)
            ->with('success', "Micro-stay {$validated['hours']} jam berhasil dibuat.");
    }
}
