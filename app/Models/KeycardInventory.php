<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeycardInventory extends Model
{
    use HasFactory;

    protected $table = 'keycard_inventory';

    protected $guarded = ['id'];

    protected $casts = [
        'times_reused' => 'integer',
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function keycardType() { return $this->belongsTo(KeycardType::class); }
    public function assignedRoom() { return $this->belongsTo(Room::class, 'assigned_to_room_id'); }
    public function assignedReservation() { return $this->belongsTo(Reservation::class, 'assigned_to_reservation_id'); }
    public function currentGuest() { return $this->belongsTo(Guest::class, 'current_guest_id'); }
}
