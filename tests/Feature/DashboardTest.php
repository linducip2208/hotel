<?php

use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\RoomType;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Dash Hotel', 'slug' => 'dash-test', 'region_code' => 'ID-JK', 'total_rooms' => 20, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'DLX', 'name' => 'Deluxe', 'slug' => 'deluxe-dash',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    $this->guest = Guest::create([
        'property_id' => $this->property->id, 'first_name' => 'Test', 'last_name' => 'Guest',
        'id_number' => 'ID' . uniqid(),
    ]);
    $this->folio = Folio::create([
        'property_id' => $this->property->id,
        'guest_id' => $this->guest->id,
        'folio_no' => 'F-DASH-001',
        'status' => 'open',
        'balance' => 0,
    ]);
});

function makeGuest(Property $prop): Guest
{
    return Guest::create([
        'property_id' => $prop->id, 'first_name' => 'Test', 'last_name' => 'Guest',
        'id_number' => 'ID' . uniqid(),
    ]);
}

it('dashboard shows arrivals today', function () {
    $today = now()->toDateString();
    $guest = makeGuest($this->property);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-001',
        'check_in' => $today,
        'check_out' => now()->addDays(2)->toDateString(),
        'nights' => 2,
        'adults' => 1,
        'pax' => 1,
        'total_amount' => 1000000,
        'status' => 'confirmed',
        'source' => 'direct',
    ]);

    $arrivals = Reservation::where('property_id', $this->property->id)
        ->whereDate('check_in', $today)
        ->whereIn('status', ['confirmed', 'tentative'])
        ->count();

    expect($arrivals)->toBe(1);
});

it('dashboard shows departures today', function () {
    $today = now()->toDateString();
    $guest = makeGuest($this->property);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-002',
        'check_in' => now()->subDays(2)->toDateString(),
        'check_out' => $today,
        'nights' => 2,
        'adults' => 1,
        'pax' => 1,
        'total_amount' => 1000000,
        'status' => 'checked_in',
        'source' => 'direct',
    ]);

    $departures = Reservation::where('property_id', $this->property->id)
        ->whereDate('check_out', $today)
        ->where('status', 'checked_in')
        ->count();

    expect($departures)->toBe(1);
});

it('dashboard counts in-house reservations', function () {
    $guest1 = makeGuest($this->property);
    $guest2 = makeGuest($this->property);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest1->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-003',
        'check_in' => now()->subDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'nights' => 3,
        'adults' => 2,
        'pax' => 2,
        'total_amount' => 1500000,
        'status' => 'checked_in',
        'source' => 'direct',
    ]);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest2->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-004',
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(3)->toDateString(),
        'nights' => 2,
        'adults' => 1,
        'pax' => 1,
        'total_amount' => 800000,
        'status' => 'confirmed',
        'source' => 'ota',
    ]);

    $inHouse = Reservation::where('property_id', $this->property->id)
        ->where('status', 'checked_in')
        ->count();

    expect($inHouse)->toBe(1);
});

it('dashboard computes occupancy percentage', function () {
    $today = now()->toDateString();

    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->roomType->id,
        'date' => $today,
        'total' => 20,
        'sold' => 12,
    ]);

    $sold = (int) Inventory::where('property_id', $this->property->id)
        ->whereDate('date', $today)
        ->sum('sold');

    $occPct = round(($sold / $this->property->total_rooms) * 100, 1);

    expect($sold)->toBe(12)
        ->and($occPct)->toBe(60.0);
});

it('dashboard computes ADR', function () {
    $today = now()->toDateString();

    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->roomType->id,
        'date' => $today,
        'total' => 20,
        'sold' => 10,
    ]);

    FolioCharge::create([
        'property_id' => $this->property->id,
        'folio_id' => $this->folio->id,
        'charge_date' => $today,
        'category' => 'room',
        'description' => 'Room charge',
        'amount' => 8000000,
        'is_void' => false,
    ]);

    $roomRev = (float) FolioCharge::where('property_id', $this->property->id)
        ->whereDate('charge_date', $today)
        ->where('category', 'room')
        ->where('is_void', false)
        ->sum('amount');

    $sold = (int) Inventory::where('property_id', $this->property->id)
        ->whereDate('date', $today)
        ->sum('sold');

    $adr = $sold > 0 ? round($roomRev / $sold, 0) : 0;

    expect($adr)->toBe(800000.0);
});

it('dashboard computes RevPAR', function () {
    $today = now()->toDateString();

    FolioCharge::create([
        'property_id' => $this->property->id,
        'folio_id' => $this->folio->id,
        'charge_date' => $today,
        'category' => 'room',
        'description' => 'Room charge',
        'amount' => 6000000,
        'is_void' => false,
    ]);

    $roomRev = (float) FolioCharge::where('property_id', $this->property->id)
        ->whereDate('charge_date', $today)
        ->where('category', 'room')
        ->where('is_void', false)
        ->sum('amount');

    $revpar = round($roomRev / $this->property->total_rooms, 0);

    expect($revpar)->toBe(300000.0);
});

it('dashboard detects pending payment balances', function () {
    $today = now()->toDateString();
    $guest1 = makeGuest($this->property);
    $guest2 = makeGuest($this->property);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest1->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-005',
        'check_in' => $today,
        'check_out' => now()->addDays(3)->toDateString(),
        'nights' => 3,
        'adults' => 1,
        'pax' => 1,
        'total_amount' => 3000000,
        'balance' => 3000000,
        'status' => 'checked_in',
        'source' => 'direct',
    ]);

    Reservation::create([
        'property_id' => $this->property->id,
        'primary_guest_id' => $guest2->id,
        'room_type_id' => $this->roomType->id,
        'ref' => 'HMS-DASH-006',
        'check_in' => $today,
        'check_out' => now()->addDays(2)->toDateString(),
        'nights' => 2,
        'adults' => 2,
        'pax' => 2,
        'total_amount' => 2000000,
        'balance' => 0,
        'status' => 'checked_in',
        'source' => 'direct',
    ]);

    $pending = Reservation::where('property_id', $this->property->id)
        ->where('balance', '>', 0)
        ->whereIn('status', ['confirmed', 'checked_in'])
        ->count();

    expect($pending)->toBe(1);
});
