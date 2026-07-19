<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomUpgrade extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'upgrade_fee' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function fromRoom() { return $this->belongsTo(Room::class, 'from_room_id'); }
    public function toRoom() { return $this->belongsTo(Room::class, 'to_room_id'); }
    public function processedByUser() { return $this->belongsTo(User::class, 'processed_by_user_id'); }
}
