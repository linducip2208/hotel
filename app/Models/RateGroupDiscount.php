<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateGroupDiscount extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Apply this discount to a base amount.
     */
    public function applyTo(float $amount): float
    {
        if ($this->discount_type === 'fixed') {
            return max(0, $amount - (float) $this->discount_value);
        }

        $pct = min(100, max(0, (float) $this->discount_value));

        return max(0, $amount * (1 - $pct / 100));
    }
}
