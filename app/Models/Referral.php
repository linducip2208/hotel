<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'is_rewarded' => 'boolean',
        'referred_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function property()      { return $this->belongsTo(Property::class); }
    public function referrerGuest() { return $this->belongsTo(Guest::class, 'referrer_guest_id'); }
    public function referredGuest() { return $this->belongsTo(Guest::class, 'referred_guest_id'); }
    public function referralCode()  { return $this->belongsTo(ReferralCode::class); }
}
