<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetVehicle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_maintenance_at' => 'date',
        'next_maintenance_due' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function trips() { return $this->hasMany(FleetTrip::class, 'vehicle_id'); }
}
