<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetDriver extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'license_expiry' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function trips() { return $this->hasMany(FleetTrip::class, 'driver_id'); }
}
