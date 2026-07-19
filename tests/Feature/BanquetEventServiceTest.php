<?php

use App\Models\FunctionRoom;
use App\Models\Property;
use App\Services\Banquet\EventService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Test Hotel', 'slug' => 'test-hotel-banquet', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->room = FunctionRoom::create([
        'property_id' => $this->property->id,
        'name' => 'Ballroom A', 'code' => 'BALL-A', 'capacity_theatre' => 200,
        'half_day_rate' => 5000000, 'full_day_rate' => 9000000, 'is_active' => true,
    ]);
    $this->svc = app(EventService::class);
    $this->baseData = [
        'property_id' => $this->property->id,
        'function_room_id' => $this->room->id,
        'title' => 'Test Event',
        'event_type' => 'meeting',
        'event_date' => '2026-12-01',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'expected_attendees' => 50,
    ];
});

it('creates event with auto event_no and inquiry status', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 9000000]);

    expect($event->event_no)->toStartWith('EVT-')
        ->and($event->status)->toBe('inquiry')
        ->and((float) $event->grand_total)->toBe(9000000.0);
});

it('adds menu item and recalculates grand total', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 5000000, 'title' => 'Corp Dinner']);

    $this->svc->addMenuItem($event, ['name' => 'Nasi Goreng', 'qty' => 100, 'unit_price' => 75000]);

    $event->refresh();
    expect((float) $event->fnb_total)->toBe(7500000.0)
        ->and((float) $event->grand_total)->toBe(12500000.0)
        ->and((float) $event->balance)->toBe(12500000.0);
});

it('generates beo with correct structure', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 4500000, 'title' => 'Seminar']);

    $beo = $this->svc->generateBeo($event);

    expect($beo)->toHaveKeys(['event', 'function_room', 'menu_items', 'totals'])
        ->and((float) $beo['totals']['venue'])->toBe(4500000.0);
});

it('balance reduces when deposit is paid', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 8000000, 'deposit_paid' => 2000000, 'title' => 'Gala']);

    expect((float) $event->balance)->toBe(6000000.0);
});
