<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChargeDistribution extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'distributed_at' => 'datetime',
        'total_collected' => 'decimal:2',
        'admin_share_pct' => 'decimal:3',
        'staff_share_amount' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
