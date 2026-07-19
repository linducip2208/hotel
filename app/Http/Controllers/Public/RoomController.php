<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;

class RoomController extends Controller
{
    public function index()
    {
        $property = Property::orderBy('id')->first();

        $roomTypes = $property
            ? RoomType::where('property_id', $property->id)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('base_rate')
                ->get()
            : collect();

        $rooms = $property
            ? Room::where('property_id', $property->id)
                ->where('is_active', true)
                ->with('roomType')
                ->orderBy('floor')
                ->orderBy('number')
                ->get()
            : collect();

        return view('public.rooms.index', compact('property', 'roomTypes', 'rooms'));
    }

    public function show(string $slug)
    {
        $property = Property::orderBy('id')->first();
        $roomType = RoomType::where('property_id', $property->id)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.rooms.show', compact('property', 'roomType'));
    }
}
