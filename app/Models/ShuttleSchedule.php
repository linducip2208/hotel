<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShuttleSchedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
