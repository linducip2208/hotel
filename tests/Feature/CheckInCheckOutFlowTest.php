<?php

use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\OutstandingBalanceException;
use App\Services\Fo\ReservationService;
use App\Services\Fo\ReservationValidationException;
use App\Services\Fo\RoomSoldOutException;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'FO Hotel', 'slug' => 'fo-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'standard',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    $this->plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true,
    ]);
    // Physical rooms so capacity-based availability works.
    $this->room = makeRoom($this->property, $this->roomType, '101', 'clean');
    makeRoom($this->property, $this->roomType, '102', 'clean');
    $this->svc = app(ReservationService::class);
});

function makeRoom(Property $property, RoomType $roomType, string $number, string $hk = 'clean'): Room
{
    return Room::firstOrCreate(
        ['property_id' => $property->id, 'number' => $number],
        ['room_type_id' => $roomType->id, 'hk_status' => $hk, 'fo_status' => 'vacant', 'is_active' => true]
    );
}

function makeReservation($svc, $property, $roomType, $plan): Reservation
{
    return $svc->create([
        'property_id' => $property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $roomType->id, 'rate_plan_id' => $plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Budi', 'email' => 'budi'.uniqid().'@x.com'],
    ]);
}

it('blocks check-in when no room is assigned', function () {
    config(['hotel.auto_assign_room' => false]);
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);

    expect(fn () => $this->svc->checkIn($r))
        ->toThrow(ReservationValidationException::class);
});

it('blocks check-in when room is dirty', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '101');
    $room->update(['hk_status' => 'dirty']);
    $r->rooms->first()->update(['room_id' => $room->id]);

    expect(fn () => $this->svc->checkIn($r))
        ->toThrow(ReservationValidationException::class);
});

it('blocks check-in when room is out of order', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '102', 'clean');
    $room->update(['fo_status' => 'out_of_order']);
    $r->rooms->first()->update(['room_id' => $room->id]);

    expect(fn () => $this->svc->checkIn($r))
        ->toThrow(ReservationValidationException::class);
});

it('check-in succeeds with clean assigned room and marks room occupied', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '103', 'inspected');
    $r->rooms->first()->update(['room_id' => $room->id]);

    $this->svc->checkIn($r, 1);

    expect($r->fresh()->status)->toBe('checked_in');
    expect($room->fresh()->fo_status)->toBe('occupied');
});

it('prevents duplicate check-in', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '104');
    $r->rooms->first()->update(['room_id' => $room->id]);

    $this->svc->checkIn($r);

    expect(fn () => $this->svc->checkIn($r))
        ->toThrow(ReservationValidationException::class);
});

it('blocks checkout when folio has outstanding charges', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '105');
    $r->rooms->first()->update(['room_id' => $room->id]);
    $this->svc->checkIn($r);

    // Post an unpaid minibar charge.
    $folio = $r->folios->first();
    FolioCharge::create([
        'folio_id' => $folio->id, 'property_id' => $this->property->id,
        'charge_date' => now()->toDateString(), 'description' => 'Minibar cola',
        'category' => 'minibar', 'qty' => 1, 'unit_price' => 50000, 'amount' => 50000,
    ]);
    $folio->recalculate();

    expect(fn () => $this->svc->checkOut($r, 1))
        ->toThrow(OutstandingBalanceException::class);
    expect($r->fresh()->status)->toBe('checked_in');
});

it('allows manager override checkout with reason and audit', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '106');
    $r->rooms->first()->update(['room_id' => $room->id]);
    $this->svc->checkIn($r);

    $folio = $r->folios->first();
    FolioCharge::create([
        'folio_id' => $folio->id, 'property_id' => $this->property->id,
        'charge_date' => now()->toDateString(), 'description' => 'Minibar',
        'category' => 'minibar', 'qty' => 1, 'unit_price' => 50000, 'amount' => 50000,
    ]);
    $folio->recalculate();

    // Override without reason → rejected.
    expect(fn () => $this->svc->checkOut($r, 1, true, null))
        ->toThrow(ReservationValidationException::class);

    $this->svc->checkOut($r, 1, true, 'Payment collected tomorrow');

    expect($r->fresh()->status)->toBe('checked_out');
    expect($folio->fresh()->status)->toBe('open'); // still receivable
});

it('checkout marks room dirty and closes paid folio', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '107');
    $r->rooms->first()->update(['room_id' => $room->id]);
    $this->svc->checkIn($r);

    // Guest pays exactly the grand total → folio balance 0 → folio closes at checkout.
    $folio = $r->folios->first();
    $folio->payments()->create([
        'folio_id' => $folio->id, 'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(), 'amount' => $r->grand_total,
        'method' => 'cash', 'status' => 'paid',
    ]);

    $this->svc->checkOut($r, 1);

    expect($room->fresh()->fo_status)->toBe('vacant');
    expect($room->fresh()->hk_status)->toBe('dirty');
    expect($folio->fresh()->status)->toBe('closed');
});

it('blocks cancel of checked_in reservation', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $room = makeRoom($this->property, $this->roomType, '108');
    $r->rooms->first()->update(['room_id' => $room->id]);
    $this->svc->checkIn($r);

    expect(fn () => $this->svc->cancel($r, 'test'))
        ->toThrow(ReservationValidationException::class);
});

it('release inventory when reservation cancelled after creation', function () {
    $r = makeReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $soldBefore = Inventory::where('room_type_id', $this->roomType->id)->whereDate('date', now())->first()->sold;

    $this->svc->cancel($r, 'guest request');
    $soldAfter = Inventory::where('room_type_id', $this->roomType->id)->whereDate('date', now())->first()->sold;

    expect($soldAfter)->toBe($soldBefore - 1);
});

it('throws when room type does not belong to property (cross-property attack)', function () {
    $otherProperty = Property::create([
        'name' => 'Other Hotel', 'slug' => 'other-hotel', 'region_code' => 'ID-YO', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $otherRoomType = RoomType::create([
        'property_id' => $otherProperty->id, 'code' => 'OTR', 'name' => 'Other', 'slug' => 'other',
        'max_occupancy' => 2, 'base_rate' => 400000, 'is_active' => true,
    ]);

    expect(fn () => makeReservation($this->svc, $this->property, $otherRoomType, $this->plan))
        ->toThrow(ReservationValidationException::class);
});

it('throws sold out when inventory exhausted', function () {
    // Take both physical rooms out of service → capacity 0.
    Room::where('property_id', $this->property->id)->where('room_type_id', $this->roomType->id)->update(['is_active' => false]);

    expect(fn () => makeReservation($this->svc, $this->property, $this->roomType, $this->plan))
        ->toThrow(RoomSoldOutException::class);
});

it('guest lookup is scoped to property', function () {
    $otherProperty = Property::create([
        'name' => 'Chain 2', 'slug' => 'chain-2', 'region_code' => 'ID-YO', 'total_rooms' => 5, 'is_active' => true,
    ]);
    Guest::create(['property_id' => $otherProperty->id, 'first_name' => 'Cross', 'email' => 'cross@guests.com']);

    $guest = Guest::where('property_id', $this->property->id)->where('email', 'cross@guests.com')->first();
    expect($guest)->toBeNull();
});
