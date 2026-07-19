<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'review_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'scores' => 'array',
        'goals' => 'array',
        'acknowledged_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
}
