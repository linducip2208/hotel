<?php

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Rate;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Services\Fo\ReservationService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Test Hotel', 'slug' => 'test-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'standard',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    $this->ratePlan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_refundable' => true, 'is_active' => true,
    ]);
    foreach (range(0, 6) as $d) {
        $date = now()->addDays($d)->toDateString();
        Rate::create([
            'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
            'rate_plan_id' => $this->ratePlan->id, 'date' => $date, 'amount' => 500000, 'currency' => 'IDR',
        ]);
        Inventory::create([
            'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
            'date' => $date, 'total' => 5,
        ]);
    }
});

it('creates reservation with auto folio', function () {
    $svc = app(ReservationService::class);
    $r = $svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(3)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->ratePlan->id, 'adults' => 2]],
        'primary_guest' => ['first_name' => 'John', 'email' => 'john@example.com'],
        'source' => 'direct',
    ]);
    expect($r->status)->toBe('confirmed');
    expect($r->nights)->toBe(2);
    expect($r->total_room)->toEqual(1000000.0);
    expect($r->folios()->count())->toBe(1);
});

it('decrements inventory on reservation', function () {
    $svc = app(ReservationService::class);
    $svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->ratePlan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Jane', 'email' => 'jane@example.com'],
    ]);
    $inv = Inventory::where('room_type_id', $this->roomType->id)->whereDate('date', now()->addDay())->first();
    expect($inv->sold)->toBe(1);
});

it('cancels reservation and releases inventory', function () {
    $svc = app(ReservationService::class);
    $r = $svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->ratePlan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Test', 'email' => 't@x.com'],
    ]);
    $svc->cancel($r, 'Customer changed mind', 0);
    expect($r->fresh()->status)->toBe('cancelled');
    $inv = Inventory::where('room_type_id', $this->roomType->id)->whereDate('date', now()->addDay())->first();
    expect($inv->sold)->toBe(0);
});
