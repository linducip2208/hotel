<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestRequest extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'opened_at' => 'datetime',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
    public function room()        { return $this->belongsTo(Room::class); }
    public function assignee()    { return $this->belongsTo(User::class, 'assignee_id'); }

    public function markResponded(): void
    {
        $this->responded_at = now();
        $this->response_minutes = $this->opened_at->diffInMinutes(now());
        $this->save();
    }

    public function markResolved(?string $notes = null): void
    {
        $this->resolved_at = now();
        $this->resolution_minutes = $this->opened_at->diffInMinutes(now());
        $this->status = 'resolved';
        $this->resolution_notes = $notes;
        $this->save();
    }
}
