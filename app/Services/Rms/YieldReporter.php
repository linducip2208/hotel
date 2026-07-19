<?php

namespace App\Services\Rms;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class YieldReporter
{
    /**
     * RevPAR (Revenue per available room), ADR (Avg daily rate), MPI (Market penetration index — needs comp set).
     * For MVP: RevPAR + ADR + occupancy. MPI/ARI Phase 3 with rate shopper integration.
     */
    public function summary(Property $property, Carbon $from, Carbon $to): array
    {
        $totalRevenue = (float) Reservation::where('property_id', $property->id)
            ->whereBetween('check_in', [$from, $to])
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->sum('total_room');

        $totalNights = (int) Reservation::where('property_id', $property->id)
            ->whereBetween('check_in', [$from, $to])
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->sum('nights');

        $totalRoomsAvailable = $property->total_rooms * $from->diffInDays($to);

        $adr = $totalNights > 0 ? round($totalRevenue / $totalNights, 2) : 0;
        $revpar = $totalRoomsAvailable > 0 ? round($totalRevenue / $totalRoomsAvailable, 2) : 0;
        $occupancy = $totalRoomsAvailable > 0 ? round(($totalNights / $totalRoomsAvailable) * 100, 2) : 0;

        return [
            'period_from' => $from->toDateString(),
            'period_to' => $to->toDateString(),
            'rooms_available' => $totalRoomsAvailable,
            'rooms_sold' => $totalNights,
            'occupancy_pct' => $occupancy,
            'total_revenue' => $totalRevenue,
            'adr' => $adr,
            'revpar' => $revpar,
        ];
    }
}
