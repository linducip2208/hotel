<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateScraperTarget extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'ota_urls' => 'array',
        'room_type_mapping' => 'array',
        'is_active' => 'boolean',
        'distance_km' => 'decimal:1',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function logs() { return $this->hasMany(RateScraperLog::class); }
}
