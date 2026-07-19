<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'photos' => 'array',
    ];

    public function pointsOfInterest() { return $this->hasMany(PointOfInterest::class); }
}
