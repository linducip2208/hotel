<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'reported_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'cost_material' => 'decimal:2',
        'cost_labor' => 'decimal:2',
        'material_used' => 'array',
        'photos_before' => 'array',
        'photos_after' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
    public function oooPeriod(){ return $this->hasOne(OutOfOrderPeriod::class); }
}
