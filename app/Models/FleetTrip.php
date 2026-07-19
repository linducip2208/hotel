<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetTrip extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
        'charge_amount' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function vehicle() { return $this->belongsTo(FleetVehicle::class); }
    public function driver() { return $this->belongsTo(FleetDriver::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function folioCharge() { return $this->belongsTo(FolioCharge::class); }
}
