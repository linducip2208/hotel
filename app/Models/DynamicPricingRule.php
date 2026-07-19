<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingRule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'threshold_low'      => 'decimal:2',
        'threshold_high'     => 'decimal:2',
        'action_value'       => 'decimal:2',
        'min_price_floor'    => 'decimal:2',
        'max_price_ceiling'  => 'decimal:2',
        'is_active'          => 'boolean',
        'last_applied_at'    => 'datetime',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function roomType()  { return $this->belongsTo(RoomType::class); }
    public function channel()   { return $this->belongsTo(Channel::class); }
    public function logs()      { return $this->hasMany(DynamicPricingLog::class, 'rule_id'); }
}
