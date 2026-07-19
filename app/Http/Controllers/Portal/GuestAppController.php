<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class GuestAppController extends Controller
{
    public function dashboard()
    {
        $guest = auth('customer')->user();
        $activeStay = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->with(['roomType', 'room'])
            ->latest('check_in')
            ->first();

        return view('portal.guest.dashboard', compact('guest', 'activeStay'));
    }

    public function chat()
    {
        $guest = auth('customer')->user();
        $property = \App\Models\Property::find($guest->property_id);

        return view('portal.guest.chat', compact('guest', 'property'));
    }
}
