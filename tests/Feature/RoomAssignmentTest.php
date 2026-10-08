<?php

use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Fo\ReservationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->property = Property::create([
        'name' => 'Assign Hotel', 'slug' => 'assign-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'std-assign',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    $this->roomA = Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'A-101', 'floor' => 1, 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->roomB = Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'A-102', 'floor' => 1, 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Assign Admin', 'email' => 'assign-admin@x.com',
        'password' => bcrypt('password-password'), 'property_id' => $this->property->id,
    ]);
    $this->user->assignRole('super_owner');
    $this->actingAs($this->user);
    $this->svc = app(ReservationService::class);
    config(['hotel.auto_assign_room' => false]);
});

function unassignedReservation($svc, $property, $roomType, $plan): Reservation
{
    return $svc->create([
        'property_id' => $property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $roomType->id, 'rate_plan_id' => $plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Assign', 'email' => 'assign'.uniqid().'@x.com'],
    ]);
}

it('assignment board lists unassigned reservation rooms using the real model', function () {
    $reservation = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);

    $response = $this->get(route('panel.fo.room-assignment.index'));
    $response->assertOk();
    $response->assertSee($reservation->ref);
});

it('assigns a physical room to the reservation-room row via HTTP', function () {
    $reservation = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $rr = $reservation->rooms->first();
    expect($rr->room_id)->toBeNull();

    $response = $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $rr->id,
        'room_id' => $this->roomA->id,
    ]);

    $response->assertSessionHasNoErrors();
    expect((int) $rr->fresh()->room_id)->toBe($this->roomA->id);
    // Future arrival → reserved, not occupied.
    expect($this->roomA->fresh()->fo_status)->toBe('reserved');
});

it('rejects assigning a room already booked for overlapping dates', function () {
    // First reservation takes room A.
    $first = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $firstRr = $first->rooms->first();
    $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $firstRr->id,
        'room_id' => $this->roomA->id,
    ])->assertSessionHasNoErrors();

    // Second reservation tries the same room.
    $second = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $secondRr = $second->rooms->first();
    $this->roomB->update(['is_active' => false]); // only room A remains

    $response = $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $secondRr->id,
        'room_id' => $this->roomA->id,
    ]);

    $response->assertSessionHasErrors('assign');
    expect($secondRr->fresh()->room_id)->toBeNull();
});

it('rejects cross-property room assignment', function () {
    $other = Property::create(['name' => 'Other Assign', 'slug' => 'other-assign', 'region_code' => 'ID-YO', 'total_rooms' => 5, 'is_active' => true]);
    $otherRoom = Room::create([
        'property_id' => $other->id, 'room_type_id' => $this->roomType->id,
        'number' => 'O-201', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);

    $reservation = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $rr = $reservation->rooms->first();

    $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $rr->id,
        'room_id' => $otherRoom->id,
    ])->assertSessionHasErrors('room_id');

    expect($rr->fresh()->room_id)->toBeNull();
});

it('swaps rooms between two assigned reservation rooms', function () {
    $r1 = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $r2 = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);

    $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $r1->rooms->first()->id, 'room_id' => $this->roomA->id,
    ])->assertSessionHasNoErrors();
    $this->post(route('panel.fo.room-assignment.assign'), [
        'reservation_room_id' => $r2->rooms->first()->id, 'room_id' => $this->roomB->id,
    ])->assertSessionHasNoErrors();

    $this->post(route('panel.fo.room-assignment.swap'), [
        'reservation_room_a' => $r1->rooms->first()->id,
        'reservation_room_b' => $r2->rooms->first()->id,
    ])->assertSessionHasNoErrors();

    expect((int) $r1->rooms->first()->fresh()->room_id)->toBe($this->roomB->id);
    expect((int) $r2->rooms->first()->fresh()->room_id)->toBe($this->roomA->id);
});

it('auto-assign fills unassigned rooms without conflict', function () {
    $r1 = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);
    $r2 = unassignedReservation($this->svc, $this->property, $this->roomType, $this->plan);

    $this->post(route('panel.fo.room-assignment.auto'), ['date' => now()->toDateString()])
        ->assertSessionHasNoErrors();

    $roomIds = [
        (int) $r1->rooms->first()->fresh()->room_id,
        (int) $r2->rooms->first()->fresh()->room_id,
    ];
    dump('roomIds: '.json_encode($roomIds));
    // Two different rooms, no conflict.
    expect($roomIds[0])->not->toBe($roomIds[1]);
});
