<?php

namespace App\Services\Sustainability;

use App\Models\CarbonFootprint;
use App\Models\Reservation;

class CarbonCalculator
{
    // Indonesia grid emission factor ~0.85 kg CO2e/kWh (2024 estimate)
    public const KWH_PER_ROOM_NIGHT = 30;
    public const WATER_LITERS_PER_NIGHT = 250;
    public const WASTE_KG_PER_NIGHT = 1.2;
    public const GRID_CO2_FACTOR = 0.85;

    public function estimateForReservation(Reservation $r): CarbonFootprint
    {
        $nights = max(1, (int) $r->nights);
        $rooms = max(1, $r->rooms->count() ?: 1);

        $energy = self::KWH_PER_ROOM_NIGHT * $rooms * $nights;
        $water = self::WATER_LITERS_PER_NIGHT * $rooms * $nights;
        $waste = self::WASTE_KG_PER_NIGHT * $rooms * $nights;
        $co2 = round($energy * self::GRID_CO2_FACTOR + $waste * 0.5, 2);

        return CarbonFootprint::updateOrCreate(
            ['reservation_id' => $r->id],
            [
                'property_id' => $r->property_id,
                'energy_kwh' => $energy,
                'water_liters' => $water,
                'waste_kg' => $waste,
                'co2e_kg' => $co2,
                'period_date' => $r->check_in,
                'breakdown' => [
                    'rooms' => $rooms,
                    'nights' => $nights,
                    'kwh_per_night' => self::KWH_PER_ROOM_NIGHT,
                    'grid_factor' => self::GRID_CO2_FACTOR,
                ],
            ]
        );
    }
}
