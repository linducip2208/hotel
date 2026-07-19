<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateShopperSnapshot extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'check_date' => 'date',
        'shopped_for_date' => 'date',
        'competitor_set' => 'array',
        'our_rate' => 'decimal:2',
        'avg_competitor_rate' => 'decimal:2',
        'rate_index' => 'decimal:3',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function provider() { return $this->belongsTo(Provider::class); }
}
