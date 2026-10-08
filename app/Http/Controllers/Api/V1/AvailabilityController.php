<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Services\Fo\PricingService;
use App\Services\PublicPropertyResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request, PricingService $pricing)
    {
        $r = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $property = app('current_property') ?? app(PublicPropertyResolver::class)->resolve();
        if (! $property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        $checkIn = Carbon::parse($r['check_in'])->startOfDay();
        $checkOut = Carbon::parse($r['check_out'])->startOfDay();
        $nights = $checkIn->diffInDays($checkOut);

        $types = RoomType::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('max_occupancy', '>=', $r['adults'] ?? 1)
            ->get()
            ->map(function ($rt) use ($property, $pricing, $checkIn, $checkOut, $nights) {
                // Same pricing engine as the booking engine and ReservationService.
                $plans = $pricing->ratePlansForStay($property, $rt->id, $checkIn, $checkOut, $nights);
                $sellable = $plans->filter(fn ($p) => $p['sellable']);

                return [
                    'id' => $rt->id,
                    'name' => $rt->name,
                    'slug' => $rt->slug,
                    'max_occupancy' => $rt->max_occupancy,
                    'available' => true,
                    'rate_plans' => $plans->values(),
                    'from_total' => $sellable->min('total'),
                ];
            });

        return response()->json(['data' => $types]);
    }
}
