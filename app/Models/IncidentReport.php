<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolved_at' => 'datetime',
        'police_report_filed' => 'boolean',
        'insurance_claim_filed' => 'boolean',
        'photos' => 'array',
    ];

    public function property()          { return $this->belongsTo(Property::class); }
    public function reportedBy()        { return $this->belongsTo(User::class, 'reported_by_user_id'); }
    public function resolvedBy()        { return $this->belongsTo(User::class, 'resolved_by_user_id'); }
    public function guest()             { return $this->belongsTo(Guest::class); }
    public function reservation()       { return $this->belongsTo(Reservation::class); }
    public function room()              { return $this->belongsTo(Room::class); }
    public function followups()         { return $this->hasMany(IncidentFollowup::class); }

    public function severityColor(): string
    {
        return match($this->severity) {
            'low' => 'green',
            'medium' => 'amber',
            'high' => 'red',
            'critical' => 'purple',
            default => 'gray',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'open' => 'red',
            'investigating' => 'amber',
            'resolved' => 'emerald',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function typeLabel(): string
    {
        return match($this->incident_type) {
            'guest_injury' => 'Cedera Tamu',
            'guest_illness' => 'Sakit Tamu',
            'theft' => 'Pencurian',
            'property_damage' => 'Kerusakan Properti',
            'staff_injury' => 'Cedera Staf',
            'security' => 'Keamanan',
            'fire' => 'Kebakaran',
            'flood' => 'Banjir',
            'complaint' => 'Keluhan',
            'other' => 'Lainnya',
            default => $this->incident_type,
        };
    }
}
