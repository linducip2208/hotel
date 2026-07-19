<?php

use App\Models\CashierShift;
use App\Models\DailyFlashReport;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Fo\CashierShiftService;
use App\Services\Reports\DailyFlashService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Report Hotel', 'slug' => 'report-test', 'region_code' => 'ID-JK', 'total_rooms' => 15, 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Finance Mgr', 'email' => 'finreport@test.com', 'password' => bcrypt('password'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'std-report',
        'max_occupancy' => 2, 'base_rate' => 400000, 'is_active' => true,
    ]);
});

it('builds daily flash report', function () {
    $svc = app(DailyFlashService::class);

    $report = $svc->build($this->property, now());

    expect($report)->not->toBeNull()
        ->and($report->property_id)->toBe($this->property->id);
});

it('daily flash report has rooms_kpi', function () {
    $svc = app(DailyFlashService::class);

    $date = now()->subDay();
    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->roomType->id,
        'date' => $date->toDateString(),
        'total' => 10,
        'sold' => 6,
    ]);

    $report = $svc->build($this->property, $date->copy());

    expect($report->rooms_kpi)->toBeArray();
});

it('cashier shift report generates correct data', function () {
    $cashier = User::create([
        'name' => 'Cashier X', 'email' => 'cashx@test.com', 'password' => bcrypt('x'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
    $svc = app(CashierShiftService::class);

    $shift = $svc->open($cashier, 500000);
    $svc->close($shift, 650000, 'Close test');

    $shift->refresh();

    expect((float) $shift->opening_float)->toEqual(500000.0)
        ->and((float) $shift->actual_cash)->toEqual(650000.0)
        ->and((float) $shift->cash_variance)->toEqual(150000.0)
        ->and($shift->closed_at)->not->toBeNull();
});

it('cashier shift produces surplus variance', function () {
    $cashier = User::create([
        'name' => 'Cashier Y', 'email' => 'cashy@test.com', 'password' => bcrypt('y'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
    $svc = app(CashierShiftService::class);

    $shift = $svc->open($cashier, 200000);
    $svc->close($shift, 200000, 'Even');

    $shift->refresh();
    expect((float) $shift->cash_variance)->toEqual(0.0);
});

it('cashier shift produces deficit variance', function () {
    $cashier = User::create([
        'name' => 'Cashier Z', 'email' => 'cashz@test.com', 'password' => bcrypt('z'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
    $svc = app(CashierShiftService::class);

    $shift = $svc->open($cashier, 300000);
    $svc->close($shift, 250000, 'Short');

    $shift->refresh();
    expect((float) $shift->cash_variance)->toEqual(-50000.0);
});

it('shifts are scoped by property', function () {
    $otherProperty = Property::create([
        'name' => 'Other', 'slug' => 'other-prop', 'region_code' => 'ID-JT', 'total_rooms' => 5, 'is_active' => true,
    ]);

    $cashier = User::create([
        'name' => 'Multi', 'email' => 'multi@test.com', 'password' => bcrypt('x'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
    $svc = app(CashierShiftService::class);
    $svc->open($cashier, 400000);

    $myShifts = CashierShift::where('property_id', $this->property->id)->count();
    $otherShifts = CashierShift::where('property_id', $otherProperty->id)->count();

    expect($myShifts)->toBe(1)
        ->and($otherShifts)->toBe(0);
});

it('daily flash report persists to database', function () {
    $svc = app(DailyFlashService::class);

    $svc->build($this->property, now());

    $count = DailyFlashReport::where('property_id', $this->property->id)->count();
    expect($count)->toBeGreaterThan(0);
});
