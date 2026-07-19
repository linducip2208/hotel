<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateScraperAlert extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'price_gap_pct' => 'decimal:2',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function scraperLog() { return $this->belongsTo(RateScraperLog::class); }
}
