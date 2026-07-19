<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableReservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function restaurantTable() { return $this->belongsTo(RestaurantTable::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function bookedBy() { return $this->belongsTo(User::class, 'booked_by_user_id'); }
}
