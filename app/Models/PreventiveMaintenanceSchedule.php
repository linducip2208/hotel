<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceSchedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'next_due_at' => 'date',
        'last_done_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
}
