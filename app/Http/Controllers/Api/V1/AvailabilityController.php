<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Rate;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $r = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'nullable|integer|min:1',
        ]);

        $property = Property::firstOrFail();
        $checkIn = Carbon::parse($r['check_in']);
        $checkOut = Carbon::parse($r['check_out']);

        $types = RoomType::where('property_id', $property->id)->where('is_active', true)->get()
            ->map(function ($rt) use ($property, $checkIn, $checkOut) {
                $sum = Rate::where('property_id', $property->id)
                    ->where('room_type_id', $rt->id)
                    ->whereBetween('date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
                    ->where('closed', false)
                    ->sum('amount');
                return [
                    'id' => $rt->id, 'name' => $rt->name, 'slug' => $rt->slug,
                    'max_occupancy' => $rt->max_occupancy,
                    'total' => (float) $sum,
                ];
            });

        return response()->json(['data' => $types]);
    }
}
