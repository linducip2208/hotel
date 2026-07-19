<?php

namespace App\Http\Controllers\Panel\GuestPortal;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $guests = Guest::where('property_id', app('current_property')->id)
            ->when($request->query('q'), fn ($q, $term) => $q->where(function ($qq) use ($term) {
                $qq->where('first_name', 'like', "%$term%")
                   ->orWhere('last_name', 'like', "%$term%")
                   ->orWhere('email', 'like', "%$term%")
                   ->orWhere('phone', 'like', "%$term%");
            }))->paginate(50);
        return view('panel.guests.index', compact('guests'));
    }

    public function show(int $id)
    {
        $guest = Guest::with('reservations.rooms.roomType')->findOrFail($id);
        return view('panel.guests.show', compact('guest'));
    }

    public function update(Request $request, int $id)
    {
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($id);
        $guest->update($request->only(['first_name', 'last_name', 'email', 'phone', 'preferences', 'tags']));
        return back();
    }
}
