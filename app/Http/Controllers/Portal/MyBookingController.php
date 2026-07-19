<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class MyBookingController extends Controller
{
    public function index()
    {
        $guest = auth('customer')->user();

        $bookings = Reservation::where('primary_guest_id', $guest->id)
            ->with(['roomType', 'room', 'folios'])
            ->orderByDesc('check_in')
            ->get();

        $activeStay = $bookings->first(fn ($b) => in_array($b->status, ['checked_in', 'confirmed'])
            && $b->check_in <= now() && $b->check_out >= now());

        return view('portal.guest.my-booking', compact('guest', 'bookings', 'activeStay'));
    }

    public function show($id)
    {
        $guest = auth('customer')->user();
        $booking = Reservation::where('primary_guest_id', $guest->id)
            ->with(['roomType', 'room', 'folios.charges', 'folios.payments', 'addons'])
            ->findOrFail($id);

        return view('portal.guest.my-booking-detail', compact('guest', 'booking'));
    }
}
