<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'purchased_at' => 'date',
        'disposed_at' => 'date',
        'photos' => 'array',
        'purchase_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }

    public function monthlyDepreciation(): float
    {
        if (! $this->useful_life_years || ! $this->purchase_cost) return 0;
        $depreciable = (float) $this->purchase_cost - (float) $this->residual_value;
        return round($depreciable / ($this->useful_life_years * 12), 2);
    }
}
