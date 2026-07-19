<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitlistEntry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'notified_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function preferredRoomType() { return $this->belongsTo(RoomType::class, 'preferred_room_type_id'); }
}
