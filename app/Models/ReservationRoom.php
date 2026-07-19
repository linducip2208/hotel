<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationRoom extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'subtotal' => 'decimal:2',
        'per_night_rates' => 'array',
    ];

    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
    public function ratePlan() { return $this->belongsTo(RatePlan::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function guests() { return $this->hasMany(ReservationGuest::class); }
}
