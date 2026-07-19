<?php

use App\Models\Inventory;
use App\Models\Property;
use App\Models\RoomType;
use App\Services\Fo\WaitlistService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Waitlist Hotel', 'slug' => 'waitlist-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 20, 'is_active' => true,
    ]);
    $this->rt = RoomType::create([
        'property_id' => $this->property->id,
        'name' => 'Standard', 'code' => 'STD', 'slug' => 'std-waitlist',
        'base_rate' => 400000, 'max_occupancy' => 2, 'is_active' => true,
    ]);
    $this->svc = app(WaitlistService::class);
});

it('adds waitlist entry with waiting status', function () {
    $entry = $this->svc->add([
        'property_id' => $this->property->id,
        'first_name' => 'Dewi',
        'check_in' => '2026-07-10',
        'check_out' => '2026-07-12',
        'rooms' => 1,
        'preferred_room_type_id' => $this->rt->id,
    ]);

    expect($entry->status)->toBe('waiting')
        ->and($entry->first_name)->toBe('Dewi');
});

it('notifies waitlist entry when inventory is available', function () {
    // Seed inventory with available rooms
    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->rt->id,
        'date' => '2026-07-10',
        'total' => 5, 'sold' => 0, 'blocked' => 0, 'out_of_order' => 0,
    ]);
    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->rt->id,
        'date' => '2026-07-11',
        'total' => 5, 'sold' => 0, 'blocked' => 0, 'out_of_order' => 0,
    ]);

    $entry = $this->svc->add([
        'property_id' => $this->property->id,
        'first_name' => 'Budi',
        'check_in' => '2026-07-10',
        'check_out' => '2026-07-12',
        'rooms' => 1,
        'preferred_room_type_id' => $this->rt->id,
    ]);

    $count = $this->svc->processNotifications($this->property->id);

    expect($count)->toBe(1)
        ->and($entry->fresh()->status)->toBe('notified')
        ->and($entry->fresh()->notified_at)->not->toBeNull();
});

it('does not notify when inventory is insufficient', function () {
    // sold = 5, blocked = 0, total = 5 → available = 0
    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->rt->id,
        'date' => '2026-08-01',
        'total' => 5, 'sold' => 5, 'blocked' => 0, 'out_of_order' => 0,
    ]);

    $this->svc->add([
        'property_id' => $this->property->id,
        'first_name' => 'Ani',
        'check_in' => '2026-08-01',
        'check_out' => '2026-08-02',
        'rooms' => 1,
        'preferred_room_type_id' => $this->rt->id,
    ]);

    $count = $this->svc->processNotifications($this->property->id);
    expect($count)->toBe(0);
});
