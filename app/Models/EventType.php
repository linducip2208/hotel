<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_guests' => 'integer',
        'max_guests' => 'integer',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function bookings() { return $this->hasMany(EventBooking::class); }
}
