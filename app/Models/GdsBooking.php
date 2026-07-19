<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GdsBooking extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array', 'received_at' => 'datetime'];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
