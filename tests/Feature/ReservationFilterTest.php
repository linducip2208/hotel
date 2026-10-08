<?php

use App\Models\Property;
use App\Models\RatePlan;
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
        'name' => 'Filter Hotel', 'slug' => 'filter-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'std-filter',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    RoomType::create([
        'property_id' => $this->property->id, 'code' => 'LOW', 'name' => 'Low Occupancy', 'slug' => 'low-occ',
        'max_occupancy' => 2, 'base_rate' => 100000, 'is_active' => true,
    ]);
    $this->plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true,
    ]);
    Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'F-101', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'F-102', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Filter Admin', 'email' => 'filter-admin@x.com',
        'password' => bcrypt('password-password'), 'property_id' => $this->property->id,
    ]);
    $this->user->assignRole('super_owner');
    $this->actingAs($this->user);
    $this->svc = app(ReservationService::class);
});

it('reservation list supports ref and guest search', function () {
    $alpha = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Budi', 'email' => 'budi-search@x.com'],
    ]);
    $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Sinta', 'email' => 'sinta@x.com'],
    ]);

    // Search by guest name.
    $this->get(route('panel.fo.reservations.index', ['q' => 'Budi']))
        ->assertOk()
        ->assertSee($alpha->ref)
        ->assertDontSee('Sinta');

    // Search by ref.
    $this->get(route('panel.fo.reservations.index', ['q' => $alpha->ref]))
        ->assertOk()
        ->assertSee($alpha->ref);
});

it('reservation list filters by status and shows a no-result state', function () {
    $r = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Cari', 'email' => 'cari@x.com'],
    ]);

    $this->get(route('panel.fo.reservations.index', ['status' => 'checked_in']))
        ->assertOk()
        ->assertSee('Tidak ada hasil untuk filter ini');

    $this->svc->checkIn($r, $this->user->id);

    $this->get(route('panel.fo.reservations.index', ['status' => 'checked_in']))
        ->assertOk()
        ->assertSee($r->ref);
});
