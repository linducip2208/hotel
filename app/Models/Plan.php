<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_default_signup' => 'boolean',
        'monthly_price_idr' => 'decimal:2',
        'yearly_price_idr' => 'decimal:2',
        'per_room_price_idr' => 'decimal:2',
    ];

    public function tenants()       { return $this->hasMany(Tenant::class); }
    public function subscriptions() { return $this->hasMany(TenantSubscription::class); }
}
