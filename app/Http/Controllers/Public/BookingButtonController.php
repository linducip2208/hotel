<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BookingButtonController extends Controller
{
    public function show(Request $request)
    {
        $property = app()->bound('current_property') ? app('current_property') : Property::orderBy('id')->first();

        $roomType = null;
        if ($request->filled('room_type') && $property) {
            $roomType = RoomType::where('property_id', $property->id)
                ->where('slug', $request->query('room_type'))
                ->first();
        }

        return view('public.widgets.book-button', compact('property', 'roomType'));
    }
}
