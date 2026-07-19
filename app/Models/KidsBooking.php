<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidsBooking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'child_age' => 'integer',
        'booking_date' => 'date',
        'start_time' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function activity() { return $this->belongsTo(KidsActivity::class, 'kids_activity_id'); }
}
