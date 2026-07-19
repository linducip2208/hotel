<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyOwner extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'ownership_pct' => 'decimal:2',
        'investment_amount' => 'decimal:2',
        'joined_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function distributions() { return $this->hasMany(OwnerDistribution::class, 'owner_user_id', 'user_id')->where('property_id', $this->property_id); }
    public function documents() { return $this->hasMany(OwnerDocument::class, 'owner_user_id', 'user_id')->where('property_id', $this->property_id); }
}
