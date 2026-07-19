<?php

namespace App\Services\Revenue;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\RoomType;
use Carbon\Carbon;

class OverbookingService
{
    public function calculateSafeOverbooking(Property $property, int $roomTypeId, Carbon $date): int
    {
        $totalBooked = Reservation::where('property_id', $property->id)
            ->whereHas('rooms', fn ($q) => $q->where('room_type_id', $roomTypeId))
            ->whereDate('check_in', '>=', now()->subDays(90))
            ->whereDate('check_in', '<', now()->startOfDay())
            ->count();

        $noShows = Reservation::where('property_id', $property->id)
            ->whereHas('rooms', fn ($q) => $q->where('room_type_id', $roomTypeId))
            ->whereDate('check_in', '>=', now()->subDays(90))
            ->whereDate('check_in', '<', now()->startOfDay())
            ->where('status', 'no_show')
            ->count();

        $noShowRate = $totalBooked > 0 ? $noShows / $totalBooked : 0.05;

        $dowCancels = Reservation::where('property_id', $property->id)
            ->whereHas('rooms', fn ($q) => $q->where('room_type_id', $roomTypeId))
            ->whereDate('check_in', '>=', now()->subDays(30))
            ->whereDate('check_in', '<', now()->startOfDay())
            ->whereRaw('DAYOFWEEK(check_in) = ?', [$date->dayOfWeek + 1])
            ->whereIn('status', ['cancelled'])
            ->count();

        $inventory = Inventory::where('property_id', $property->id)
            ->where('room_type_id', $roomTypeId)
            ->whereDate('date', $date->toDateString())
            ->first();

        $totalRooms = $inventory->total ?? 10;
        $cancellationRate = max($totalBooked > 0 ? $dowCancels / $totalBooked : 0.02, 0);

        return (int) floor($totalRooms * ($noShowRate + $cancellationRate * 0.5));
    }

    public function getOverbookingRisk(Property $property, Carbon $date): array
    {
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->get();
        $risks = [];

        foreach ($roomTypes as $rt) {
            $inv = Inventory::where('property_id', $property->id)
                ->where('room_type_id', $rt->id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if (! $inv) {
                continue;
            }

            $booked = (int) ($inv->sold ?? 0);
            $total = (int) ($inv->total ?? 0);
            $safeOverbook = $this->calculateSafeOverbooking($property, $rt->id, $date);
            $occupancy = $total > 0 ? round(($booked / $total) * 100, 1) : 0;

            $risks[] = [
                'room_type' => $rt->name,
                'room_type_id' => $rt->id,
                'total' => $total,
                'booked' => $booked,
                'blocked' => (int) ($inv->blocked ?? 0),
                'occupancy_pct' => $occupancy,
                'safe_overbook' => $safeOverbook,
                'risk_level' => $occupancy >= 100 ? 'critical' : ($occupancy >= 90 ? 'high' : ($occupancy >= 75 ? 'medium' : 'low')),
            ];
        }

        return $risks;
    }

    public function suggestMitigation(array $risks): array
    {
        $actions = [];

        foreach ($risks as $risk) {
            if ($risk['risk_level'] === 'critical') {
                $actions[] = "STOP SELL {$risk['room_type']} — fully booked ({$risk['occupancy_pct']}%). Consider walk-in to partner hotel.";
            } elseif ($risk['risk_level'] === 'high') {
                $actions[] = "{$risk['room_type']}: close discount rates. Safe overbooking: {$risk['safe_overbook']} rooms.";
            }
        }

        return $actions;
    }
}
