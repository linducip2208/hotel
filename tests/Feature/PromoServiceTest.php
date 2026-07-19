<?php

use App\Models\Property;
use App\Models\PromoCode;
use App\Services\Promo\PromoService;

beforeEach(function () {
    $this->property = Property::create(['name' => 'Test', 'slug' => 'test', 'region_code' => 'ID-JK', 'total_rooms' => 10]);
});

it('looks up active promo code', function () {
    PromoCode::create([
        'property_id' => $this->property->id,
        'code' => 'SUMMER25',
        'discount_type' => 'pct',
        'discount_value' => 25,
        'is_active' => true,
    ]);
    $svc = new PromoService();
    $promo = $svc->lookup('SUMMER25', $this->property->id);
    expect($promo)->not->toBeNull();
});

it('applies percentage discount', function () {
    $promo = PromoCode::create([
        'property_id' => $this->property->id,
        'code' => 'OFF20',
        'discount_type' => 'pct',
        'discount_value' => 20,
        'is_active' => true,
    ]);
    $svc = new PromoService();
    $r = $svc->apply($promo, 1000000);
    expect($r['ok'])->toBeTrue();
    expect($r['discount'])->toBe(200000.0);
    expect($r['subtotal_after'])->toBe(800000.0);
});
