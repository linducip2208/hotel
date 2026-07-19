<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentFollowup extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function property()          { return $this->belongsTo(Property::class); }
    public function incidentReport()    { return $this->belongsTo(IncidentReport::class); }
    public function assignedTo()        { return $this->belongsTo(User::class, 'assigned_to_user_id'); }

    public function isOverdue(): bool
    {
        return !$this->completed_at && $this->due_date && $this->due_date->isPast();
    }
}
