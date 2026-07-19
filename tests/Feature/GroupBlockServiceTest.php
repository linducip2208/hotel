<?php

use App\Models\GroupBlock;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\RoomType;
use App\Services\Fo\GroupBlockService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Group Hotel', 'slug' => 'group-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 50, 'is_active' => true,
    ]);
    $this->rt = RoomType::create([
        'property_id' => $this->property->id,
        'name' => 'Deluxe', 'code' => 'DLX', 'slug' => 'deluxe-grp',
        'base_rate' => 600000, 'max_occupancy' => 2, 'is_active' => true,
    ]);
    $this->svc = app(GroupBlockService::class);
});

it('creates group block with auto block_code and tentative status', function () {
    $block = $this->svc->create([
        'property_id' => $this->property->id,
        'group_name' => 'ASEAN Summit 2026',
        'check_in' => '2026-08-01',
        'check_out' => '2026-08-03',
        'rooms' => [['room_type_id' => $this->rt->id, 'rooms_count' => 10, 'rate' => 550000]],
    ]);

    expect($block->block_code)->toStartWith('GRP-')
        ->and($block->status)->toBe('tentative')
        ->and($block->rooms_count)->toBe(10);
});

it('creates master folio linked to group block', function () {
    $block = $this->svc->create([
        'property_id' => $this->property->id,
        'group_name' => 'Corporate Retreat',
        'check_in' => '2026-09-10',
        'check_out' => '2026-09-12',
        'rooms' => [['room_type_id' => $this->rt->id, 'rooms_count' => 5]],
    ]);

    expect($block->master_folio_id)->not->toBeNull()
        ->and($block->masterFolio->type)->toBe('master');
});

it('blocks inventory for each night in range', function () {
    $this->svc->create([
        'property_id' => $this->property->id,
        'group_name' => 'Inventory Test Group',
        'check_in' => '2026-10-01',
        'check_out' => '2026-10-03',
        'rooms' => [['room_type_id' => $this->rt->id, 'rooms_count' => 8]],
    ]);

    // check_in to check_out exclusive = 2 nights
    $blocked = Inventory::where('property_id', $this->property->id)
        ->where('room_type_id', $this->rt->id)
        ->whereBetween('date', ['2026-10-01', '2026-10-02'])
        ->get();

    expect($blocked)->toHaveCount(2)
        ->and($blocked->every(fn ($i) => $i->blocked === 8))->toBeTrue();
});

it('sums rooms_count across multiple room types', function () {
    $rt2 = RoomType::create([
        'property_id' => $this->property->id,
        'name' => 'Suite', 'code' => 'STE', 'slug' => 'suite-grp',
        'base_rate' => 1200000, 'max_occupancy' => 3, 'is_active' => true,
    ]);

    $block = $this->svc->create([
        'property_id' => $this->property->id,
        'group_name' => 'Mixed Group',
        'check_in' => '2026-11-01',
        'check_out' => '2026-11-02',
        'rooms' => [
            ['room_type_id' => $this->rt->id, 'rooms_count' => 6],
            ['room_type_id' => $rt2->id, 'rooms_count' => 4],
        ],
    ]);

    expect($block->rooms_count)->toBe(10)
        ->and($block->rooms)->toHaveCount(2);
});
