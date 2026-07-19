<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\GuestRequest;
use App\Models\Reservation;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    public function index()
    {
        $guest = auth('customer')->user();

        $requests = GuestRequest::where('guest_id', $guest->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $activeStay = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->latest('check_in')
            ->first();

        return view('portal.guest.requests', compact('guest', 'requests', 'activeStay'));
    }

    public function store(Request $request)
    {
        $guest = auth('customer')->user();

        $data = $request->validate([
            'type'        => 'required|string|in:housekeeping,maintenance,extra_amenities,other',
            'description' => 'required|string|max:1000',
            'priority'    => 'nullable|string|in:low,normal,high',
        ]);

        $activeStay = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->latest('check_in')
            ->first();

        GuestRequest::create([
            'property_id'   => $guest->property_id,
            'reservation_id'=> $activeStay?->id,
            'guest_id'      => $guest->id,
            'room_id'       => $activeStay?->room_id,
            'type'          => $data['type'],
            'description'   => $data['description'],
            'priority'      => $data['priority'] ?? 'normal',
            'status'        => 'pending',
            'opened_at'     => now(),
            'source'        => 'guest_app',
        ]);

        return back()->with('success', 'Permintaan berhasil dikirim.');
    }
}
