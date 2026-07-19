<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfInterest extends Model
{
    use HasFactory;
    protected $table = 'points_of_interest';
    protected $guarded = ['id'];
    protected $casts = [
        'photos' => 'array',
        'opening_hours' => 'array',
        'is_recommended' => 'boolean',
        'is_active' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function landmark() { return $this->belongsTo(Landmark::class); }
}
