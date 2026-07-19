<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'performed_at' => 'date',
        'checklist_results' => 'array',
        'cost' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function schedule() { return $this->belongsTo(PmSchedule::class, 'pm_schedule_id'); }
    public function performedBy() { return $this->belongsTo(User::class, 'performed_by_user_id'); }
}
