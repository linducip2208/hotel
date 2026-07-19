<?php

use App\Models\CancellationPolicy;
use App\Models\Property;

it('applies graduated penalty by days_before', function () {
    $p = Property::create(['name' => 'CP', 'slug' => 'cp', 'region_code' => 'ID-JK', 'total_rooms' => 1, 'is_active' => true]);
    $policy = CancellationPolicy::create([
        'property_id' => $p->id, 'name' => 'Moderate', 'code' => 'mod', 'is_refundable' => true,
        'rules' => [
            ['days_before' => 2, 'penalty_pct' => 100],
            ['days_before' => 7, 'penalty_pct' => 50],
            ['days_before' => 999, 'penalty_pct' => 0],
        ],
        'is_active' => true,
    ]);

    expect($policy->calculatePenalty(1, 1000000))->toEqual(1000000.0); // <= 2d → 100%
    expect($policy->calculatePenalty(5, 1000000))->toEqual(500000.0);  // 3-7d → 50%
    expect($policy->calculatePenalty(30, 1000000))->toEqual(0.0);       // > 7d → 0%
});
