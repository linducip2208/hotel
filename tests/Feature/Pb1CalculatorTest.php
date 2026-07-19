<?php

use App\Models\Property;
use App\Services\Accounting\Pb1Calculator;

it('calculates default 10% PB1 when no rate record exists', function () {
    $property = new Property(['region_code' => 'ID-XX-NONE']);
    $calc = new Pb1Calculator();
    expect($calc->calculate($property, 1000000))->toBe(100000.0);
});

it('uses configured region rate from db', function () {
    \App\Models\Pb1Rate::create([
        'region_code' => 'ID-TEST',
        'region_name' => 'Test Region',
        'rate' => 12.500,
        'effective_from' => now()->subYear(),
        'is_active' => true,
    ]);
    $property = new Property(['region_code' => 'ID-TEST']);
    $calc = new Pb1Calculator();
    expect($calc->rate($property))->toBe(12.5);
    expect($calc->calculate($property, 1000000))->toBe(125000.0);
});
