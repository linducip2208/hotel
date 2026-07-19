<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmSchedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'last_done_at' => 'date',
        'next_due_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
    public function vendor() { return $this->belongsTo(Vendor::class, 'assigned_vendor_id'); }
    public function logs() { return $this->hasMany(PmLog::class); }
}
