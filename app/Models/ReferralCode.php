<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'referrer_reward_amount' => 'decimal:2',
        'referee_discount_pct' => 'decimal:3',
        'is_active' => 'boolean',
        'total_referrals' => 'integer',
        'total_rewards_earned' => 'decimal:2',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function ownerGuest()  { return $this->belongsTo(Guest::class, 'owner_guest_id'); }
    public function redemptions() { return $this->hasMany(ReferralRedemption::class); }
    public function referrals()   { return $this->hasMany(Referral::class); }
}
