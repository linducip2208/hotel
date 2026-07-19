<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'expected_guests' => 'integer',
        'total_quoted' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'setup_requirements' => 'array',
        'catering_requirements' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function eventType() { return $this->belongsTo(EventType::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function venue() { return $this->belongsTo(Room::class, 'venue_id'); }
    public function folio() { return $this->belongsTo(Folio::class); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_to_user_id'); }
    public function services() { return $this->hasMany(EventService::class); }
}
