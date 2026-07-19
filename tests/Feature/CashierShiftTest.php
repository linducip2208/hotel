<?php

use App\Models\Property;
use App\Models\User;
use App\Services\Fo\CashierShiftService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Sh', 'slug' => 'sh', 'region_code' => 'ID-JK', 'total_rooms' => 1, 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Cashier 1', 'email' => 'c1@x.com', 'password' => bcrypt('x'),
        'property_id' => $this->property->id, 'is_active' => true,
    ]);
});

it('opens shift with float', function () {
    $svc = app(CashierShiftService::class);
    $shift = $svc->open($this->user, 500000);
    expect((float) $shift->opening_float)->toEqual(500000.0);
    expect($shift->closed_at)->toBeNull();
});

it('closes shift and computes variance', function () {
    $svc = app(CashierShiftService::class);
    $shift = $svc->open($this->user, 500000);
    $svc->close($shift, 600000, 'Test close');
    $shift->refresh();
    expect((float) $shift->actual_cash)->toEqual(600000.0);
    expect((float) $shift->expected_cash)->toEqual(500000.0); // no payments yet
    expect((float) $shift->cash_variance)->toEqual(100000.0); // surplus
});

it('returns current open shift for cashier', function () {
    $svc = app(CashierShiftService::class);
    $svc->open($this->user, 0);
    $current = $svc->currentForCashier($this->user);
    expect($current)->not->toBeNull();
    expect($current->cashier_id)->toBe($this->user->id);
});
