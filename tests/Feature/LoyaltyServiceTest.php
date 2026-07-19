<?php

use App\Models\Guest;
use App\Models\LoyaltyTier;
use App\Models\Property;
use App\Models\Reservation;
use App\Services\Loyalty\LoyaltyService;

beforeEach(function () {
    $this->property = Property::create(['name' => 'Loy', 'slug' => 'loy', 'region_code' => 'ID-JK', 'total_rooms' => 1, 'is_active' => true]);
    LoyaltyTier::create(['property_id' => $this->property->id, 'name' => 'Silver', 'slug' => 'silver', 'points_threshold' => 0]);
    LoyaltyTier::create(['property_id' => $this->property->id, 'name' => 'Gold', 'slug' => 'gold', 'points_threshold' => 1000]);
});

it('enrolls guest as member', function () {
    $g = Guest::create(['property_id' => $this->property->id, 'first_name' => 'A', 'email' => 'a@x.com']);
    $member = (new LoyaltyService())->enroll($g);
    expect($member->guest_id)->toBe($g->id);
    expect($member->membership_no)->toStartWith('LM-');
});

it('awards points and upgrades tier', function () {
    $g = Guest::create(['property_id' => $this->property->id, 'first_name' => 'B', 'email' => 'b@x.com']);
    $svc = new LoyaltyService();
    $svc->enroll($g);
    $r = Reservation::create([
        'property_id' => $this->property->id, 'ref' => 'L-001', 'primary_guest_id' => $g->id,
        'check_in' => now(), 'check_out' => now()->addDays(2), 'nights' => 2, 'adults' => 1,
        'status' => 'checked_out', 'total_room' => 15000000, 'grand_total' => 15000000, 'balance' => 0, 'currency' => 'IDR',
    ]);
    $tx = $svc->awardForStay($r);
    expect($tx)->not->toBeNull();
    expect($tx->points)->toBe(1500); // 15jt × 0.0001 = 1500
    $member = $g->fresh('loyaltyMember')->loyaltyMember;
    expect($member->tier?->slug)->toBe('gold'); // crossed 1000 threshold
});
