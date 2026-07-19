<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelParityAlert extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_date'   => 'date',
        'direct_rate'  => 'decimal:2',
        'channel_rate' => 'decimal:2',
        'gap_amount'   => 'decimal:2',
        'gap_pct'      => 'decimal:4',
        'resolved_at'  => 'datetime',
    ];

    public function property()       { return $this->belongsTo(Property::class); }
    public function roomType()       { return $this->belongsTo(RoomType::class); }
    public function channel()        { return $this->belongsTo(Channel::class); }
    public function resolvedByUser() { return $this->belongsTo(User::class, 'resolved_by_user_id'); }

    public function isBreached(): bool { return $this->gap_amount < 0; }
    public function isCritical(): bool { return $this->severity === 'critical'; }
}
