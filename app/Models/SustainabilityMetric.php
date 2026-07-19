<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SustainabilityMetric extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'measurement_date' => 'date',
        'value' => 'decimal:4',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
