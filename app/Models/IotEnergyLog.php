<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotEnergyLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'energy_kwh' => 'decimal:3',
        'cost_estimate' => 'decimal:2',
        'log_date' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function device() { return $this->belongsTo(IotDevice::class, 'iot_device_id'); }
}
