<?php

namespace App\Http\Controllers\Panel\Guest;

use App\Http\Controllers\Controller;
use App\Jobs\BuildGuestProfileJob;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestProfileController extends Controller
{
    public function show(Request $request, int $guestId)
    {
        $property = $request->user()->property;
        $guest    = Guest::where('property_id', $property->id)
            ->with(['profile', 'reservations' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($guestId);
        $profile  = $guest->profile;
        return view('panel.guests.profile', compact('guest', 'profile'));
    }

    public function rebuild(Request $request, int $guestId)
    {
        $property = $request->user()->property;
        $guest    = Guest::where('property_id', $property->id)->findOrFail($guestId);
        BuildGuestProfileJob::dispatch($guest->id);
        return back()->with('success', 'Profile rebuild dijadwalkan.');
    }
}
