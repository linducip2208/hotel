<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonFootprint extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'period_date' => 'date',
        'energy_kwh' => 'decimal:2',
        'water_liters' => 'decimal:2',
        'waste_kg' => 'decimal:2',
        'co2e_kg' => 'decimal:2',
        'breakdown' => 'array',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
