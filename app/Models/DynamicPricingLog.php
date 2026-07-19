<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingLog extends Model
{
    use HasFactory;

    protected $table = 'dynamic_pricing_log';

    protected $guarded = ['id'];

    protected $casts = [
        'target_date'      => 'date',
        'price_before'     => 'decimal:2',
        'price_after'      => 'decimal:2',
        'metrics_snapshot' => 'array',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function rule()      { return $this->belongsTo(DynamicPricingRule::class, 'rule_id'); }
    public function roomType()  { return $this->belongsTo(RoomType::class); }
    public function channel()   { return $this->belongsTo(Channel::class); }
}
