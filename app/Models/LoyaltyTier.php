<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['benefits' => 'array', 'rate_discount_pct' => 'decimal:3'];

    public function property() { return $this->belongsTo(Property::class); }
    public function members() { return $this->hasMany(LoyaltyMember::class, 'tier_id'); }
}
