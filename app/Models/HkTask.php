<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HkTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'photos' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
}
