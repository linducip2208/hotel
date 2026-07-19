<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyMember extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'enrolled_at' => 'datetime',
        'tier_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function tier() { return $this->belongsTo(LoyaltyTier::class, 'tier_id'); }
    public function transactions() { return $this->hasMany(LoyaltyTransaction::class, 'member_id'); }
}
