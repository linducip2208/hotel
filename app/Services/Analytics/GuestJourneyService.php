<?php

namespace App\Services\Analytics;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Guest;
use Carbon\Carbon;

class GuestJourneyService
{
    public function getFunnel(Property $property, ?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : now()->subMonths(3);
        $to   = $to ? Carbon::parse($to) : now();

        $query = Reservation::where('property_id', $property->id)
            ->whereBetween('created_at', [$from, $to]);

        $searched   = $query->count();
        $booked     = (clone $query)->whereIn('status', ['confirmed', 'tentative', 'checked_in', 'checked_out'])->count();
        $checkedIn  = (clone $query)->whereIn('status', ['checked_in', 'checked_out'])->count();
        $checkedOut = (clone $query)->where('status', 'checked_out')->count();
        $reviewed   = Review::where('property_id', $property->id)
            ->whereBetween('created_at', [$from, $to])->count();
        $returned   = Guest::where('property_id', $property->id)
            ->whereHas('reservations', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->whereHas('reservations', fn ($q) => $q->where('created_at', '>', $to), '>=', 1)
            ->count();

        return [
            'stages' => [
                ['name' => 'Pencarian',  'count' => $searched,   'pct' => 100],
                ['name' => 'Booking',    'count' => $booked,     'pct' => $searched > 0   ? round(($booked / $searched) * 100, 1) : 0],
                ['name' => 'Check-in',   'count' => $checkedIn,  'pct' => $booked > 0     ? round(($checkedIn / $booked) * 100, 1) : 0],
                ['name' => 'Check-out',  'count' => $checkedOut, 'pct' => $checkedIn > 0  ? round(($checkedOut / $checkedIn) * 100, 1) : 0],
                ['name' => 'Ulasan',     'count' => $reviewed,   'pct' => $checkedOut > 0 ? round(($reviewed / $checkedOut) * 100, 1) : 0],
                ['name' => 'Repeat',     'count' => $returned,   'pct' => $checkedOut > 0 ? round(($returned / $checkedOut) * 100, 1) : 0],
            ],
            'dropoffs' => [
                ['stage' => 'Pencarian → Booking',  'lost' => $searched - $booked,     'rate' => $searched > 0   ? round((($searched - $booked) / $searched) * 100, 1) : 0],
                ['stage' => 'Booking → Check-in',    'lost' => $booked - $checkedIn,    'rate' => $booked > 0     ? round((($booked - $checkedIn) / $booked) * 100, 1) : 0],
                ['stage' => 'Check-in → Ulasan',     'lost' => $checkedOut - $reviewed, 'rate' => $checkedOut > 0 ? round((($checkedOut - $reviewed) / $checkedOut) * 100, 1) : 0],
                ['stage' => 'Ulasan → Repeat',       'lost' => $checkedOut - $returned, 'rate' => $checkedOut > 0 ? round((($checkedOut - $returned) / $checkedOut) * 100, 1) : 0],
            ],
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ];
    }

    public function getConversionTrend(Property $property, int $days = 30): array
    {
        $trend = [];
        for ($d = $days - 1; $d >= 0; $d--) {
            $date = now()->subDays($d)->toDateString();
            $total = Reservation::where('property_id', $property->id)
                ->whereDate('created_at', $date)->count();
            $confirmed = Reservation::where('property_id', $property->id)
                ->whereDate('created_at', $date)
                ->where('status', '!=', 'cancelled')->count();
            $rate = $total > 0 ? round(($confirmed / $total) * 100, 1) : 0;
            $trend[] = [
                'date'            => $date,
                'total'           => $total,
                'confirmed'       => $confirmed,
                'conversion_rate' => $rate,
            ];
        }
        return $trend;
    }
}
