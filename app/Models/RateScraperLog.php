<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateScraperLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'scraped_for_date' => 'date',
        'rates_found' => 'array',
        'our_price' => 'decimal:2',
        'min_competitor_price' => 'decimal:2',
        'price_gap_pct' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function target() { return $this->belongsTo(RateScraperTarget::class); }
}
