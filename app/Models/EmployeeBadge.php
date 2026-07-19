<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBadge extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function badge() { return $this->belongsTo(GamificationBadge::class, 'gamification_badge_id'); }
}
