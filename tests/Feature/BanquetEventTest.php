<?php

use App\Models\Event;
use App\Models\EventMenuItem;
use App\Models\FunctionRoom;
use App\Models\Property;
use App\Services\Banquet\EventService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Banquet Hotel', 'slug' => 'banquet-test', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->room = FunctionRoom::create([
        'property_id' => $this->property->id,
        'name' => 'Grand Ballroom', 'code' => 'GRAND-1',
        'capacity_banquet' => 300, 'full_day_rate' => 15000000, 'is_active' => true,
    ]);
    $this->svc = app(EventService::class);
    $this->baseData = [
        'property_id' => $this->property->id,
        'function_room_id' => $this->room->id,
        'title' => 'Wedding Reception',
        'event_type' => 'wedding',
        'event_date' => '2026-12-25',
        'start_time' => '18:00',
        'end_time' => '22:00',
        'expected_attendees' => 200,
    ];
});

it('creates event with auto-generated event_no', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 15000000]);

    expect($event->event_no)->toStartWith('EVT-')
        ->and($event->status)->toBe('inquiry')
        ->and((float) $event->grand_total)->toBe(15000000.0);
});

it('updates event title and status', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 8000000, 'title' => 'Original Title']);

    $event->update([
        'title' => 'Updated Conference',
        'status' => 'definite',
    ]);

    expect($event->fresh()->title)->toBe('Updated Conference')
        ->and($event->fresh()->status)->toBe('definite');
});

it('deletes event', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 5000000, 'title' => 'Temp Event']);

    $event->delete();
    expect(Event::find($event->id))->toBeNull();
});

it('calculates grand total with menu items', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 10000000, 'title' => 'Gala Dinner']);

    $this->svc->addMenuItem($event, ['name' => 'Soup', 'qty' => 100, 'unit_price' => 35000]);
    $this->svc->addMenuItem($event, ['name' => 'Main Course', 'qty' => 100, 'unit_price' => 95000]);

    $event->refresh();
    expect((float) $event->fnb_total)->toBe(13000000.0)
        ->and((float) $event->grand_total)->toBe(23000000.0);
});

it('recalculates balance when deposit is made', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 10000000, 'deposit_paid' => 5000000, 'title' => 'Corporate Event']);

    expect((float) $event->balance)->toBe(5000000.0);
});

it('generates beo with complete structure', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 7500000, 'title' => 'Product Launch']);

    $beo = $this->svc->generateBeo($event);

    expect($beo)->toHaveKeys(['event', 'function_room', 'menu_items', 'av_equipment', 'totals'])
        ->and($beo['totals'])->toHaveKeys(['venue', 'fnb', 'addons', 'grand_total', 'deposit_paid', 'balance'])
        ->and((float) $beo['totals']['grand_total'])->toBe(7500000.0);
});

it('deducts balance from deposit correctly', function () {
    $event = $this->svc->create($this->baseData + ['venue_rate' => 12000000, 'deposit_paid' => 8000000, 'title' => 'Birthday Party']);

    expect((float) $event->balance)->toBe(4000000.0)
        ->and((float) $event->deposit_paid)->toBe(8000000.0)
        ->and((float) $event->grand_total)->toBe(12000000.0);
});
