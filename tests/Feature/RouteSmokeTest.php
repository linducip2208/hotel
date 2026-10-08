<?php

use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Fo\ReservationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->property = Property::create([
        'name' => 'Smoke Hotel', 'slug' => 'smoke-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);

    $this->user = User::create([
        'name' => 'Admin Smoke',
        'email' => 'admin-smoke@x.com',
        'password' => bcrypt('password-password'),
        'property_id' => $this->property->id,
    ]);
    $this->user->assignRole('super_owner');

    $roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'std-smoke',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $roomType->id,
        'number' => 'S-101', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
});

function smokeGet(User $user, string $url): TestResponse
{
    return test()->actingAs($user)->get($url);
}

it('renders public booking pages without errors', function () {
    smokeGet($this->user, route('booking.search'))->assertOk();
});

it('renders panel dashboard and front office pages', function () {
    smokeGet($this->user, route('panel.dashboard'))->assertOk();
    smokeGet($this->user, route('panel.fo.reservations.index'))->assertOk();
    smokeGet($this->user, route('panel.fo.reservations.create'))->assertOk();
    smokeGet($this->user, route('panel.fo.arrivals'))->assertOk();
    smokeGet($this->user, route('panel.fo.departures'))->assertOk();
    smokeGet($this->user, route('panel.fo.in-house'))->assertOk();
    smokeGet($this->user, route('panel.fo.calendar'))->assertOk();
});

it('renders reservation show with readiness checklist', function () {
    $roomType = RoomType::where('property_id', $this->property->id)->where('code', 'STD')->firstOrFail();
    $plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true,
    ]);
    $reservation = app(ReservationService::class)->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $roomType->id, 'rate_plan_id' => $plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Smoke', 'email' => 'smoke@x.com'],
    ]);

    $response = smokeGet($this->user, route('panel.fo.reservations.show', $reservation->id));
    $response->assertOk();
    $response->assertSee('Check-in Readiness');
    $response->assertSee('Konfirmasi Check-out');
    $response->assertSee('Batalkan Reservasi');
});

it('renders settings pages', function () {
    smokeGet($this->user, route('panel.settings.users'))->assertOk();
    smokeGet($this->user, route('panel.settings.roles.index'))->assertOk();
    smokeGet($this->user, route('panel.settings.cancellation-policies'))->assertOk();
});

it('renders pos pages including outlet management', function () {
    smokeGet($this->user, route('panel.pos.index'))->assertOk();
    smokeGet($this->user, route('panel.pos.outlets.index'))->assertOk();
    smokeGet($this->user, route('panel.pos.outlets.create'))->assertOk();
});

it('renders accounting journal pages', function () {
    smokeGet($this->user, route('panel.accounting.journal.index'))->assertOk();
    $response = smokeGet($this->user, route('panel.accounting.journal.create'));
    $response->assertOk();
    $response->assertSee('Selisih');
});

it('renders payment settings page', function () {
    smokeGet($this->user, route('panel.settings.payments.index'))->assertOk();
});

it('pos empty state links to outlet creation, not property settings', function () {
    $response = smokeGet($this->user, route('panel.pos.index'));
    $response->assertOk();
    $content = $response->getContent();
    // The CTA must point at the outlet create flow (URL path), not property settings.
    expect($content)->toContain('/pos/outlets/create');
    expect($content)->not->toContain('panel.settings.property');
});
