<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationGuest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['is_primary' => 'boolean'];

    public function reservationRoom() { return $this->belongsTo(ReservationRoom::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
}
