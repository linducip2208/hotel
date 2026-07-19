<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_refundable' => 'boolean',
        'breakfast_included' => 'boolean',
        'is_derived' => 'boolean',
        'is_active' => 'boolean',
        'cancellation_policy' => 'array',
        'deposit_config' => 'array',
    ];

    public function property()              { return $this->belongsTo(Property::class); }
    public function parent()                { return $this->belongsTo(self::class, 'parent_rate_plan_id'); }
    public function children()              { return $this->hasMany(self::class, 'parent_rate_plan_id'); }
    public function cancellationPolicy()    { return $this->belongsTo(CancellationPolicy::class); }
    public function rates()                 { return $this->hasMany(Rate::class); }
    public function reservationRooms()      { return $this->hasMany(ReservationRoom::class); }
    public function channelRoomMappings()   { return $this->hasMany(ChannelRoomMapping::class); }
    public function allotments()            { return $this->hasMany(Allotment::class); }
}
