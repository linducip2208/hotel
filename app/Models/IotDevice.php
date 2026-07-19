<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'current_state' => 'array',
        'config' => 'array',
        'last_heartbeat_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function commands() { return $this->hasMany(IotCommand::class); }
    public function energyLogs() { return $this->hasMany(IotEnergyLog::class); }
}
