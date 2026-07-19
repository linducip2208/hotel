<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NightAudit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'audit_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'summary' => 'array',
    ];

    public function property()   { return $this->belongsTo(Property::class); }
    public function runByUser() { return $this->belongsTo(User::class, 'run_by_user_id'); }
}
