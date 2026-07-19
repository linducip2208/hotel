<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorLockEvent extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array', 'occurred_at' => 'datetime'];

    public function property()    { return $this->belongsTo(Property::class); }
    public function room()        { return $this->belongsTo(Room::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
}
