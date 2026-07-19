<?php

use App\Models\Guest;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\RoomType;
use App\Services\Sustainability\CarbonCalculator;

it('calculates carbon footprint for 2-night stay', function () {
    $p = Property::create(['name' => 'Eco', 'slug' => 'eco', 'region_code' => 'ID-JK', 'total_rooms' => 1, 'is_active' => true]);
    $rt = RoomType::create(['property_id' => $p->id, 'code' => 'STD', 'name' => 'Std', 'slug' => 'std', 'max_occupancy' => 2, 'base_rate' => 100000, 'is_active' => true]);
    $rp = RatePlan::create(['property_id' => $p->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true]);
    $g = Guest::create(['property_id' => $p->id, 'first_name' => 'A', 'email' => 'a@x.com']);
    $r = Reservation::create([
        'property_id' => $p->id, 'ref' => 'TEST-001',
        'primary_guest_id' => $g->id, 'check_in' => now(), 'check_out' => now()->addDays(2),
        'nights' => 2, 'adults' => 1, 'status' => 'confirmed',
        'total_room' => 1000000, 'grand_total' => 1100000, 'balance' => 1100000, 'currency' => 'IDR',
    ]);
    ReservationRoom::create([
        'reservation_id' => $r->id, 'room_type_id' => $rt->id, 'rate_plan_id' => $rp->id,
        'check_in' => now(), 'check_out' => now()->addDays(2), 'subtotal' => 1000000,
    ]);
    $cf = (new CarbonCalculator())->estimateForReservation($r->fresh('rooms'));
    expect((float) $cf->energy_kwh)->toEqual(60.0);
    expect((float) $cf->co2e_kg)->toBeGreaterThan(50);
});
