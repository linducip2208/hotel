<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelRoomMapping extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'config' => 'array',
        'restrictions' => 'array',
        'is_active' => 'boolean',
    ];

    public function channel() { return $this->belongsTo(Channel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
    public function ratePlan() { return $this->belongsTo(RatePlan::class); }
}
