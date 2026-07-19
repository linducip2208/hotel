<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amenities' => 'array',
        'photos' => 'array',
        'smoking' => 'boolean',
        'is_active' => 'boolean',
        'base_rate' => 'decimal:2',
    ];

    public function property()           { return $this->belongsTo(Property::class); }
    public function rooms()              { return $this->hasMany(Room::class); }
    public function rates()              { return $this->hasMany(Rate::class); }
    public function inventories()        { return $this->hasMany(Inventory::class); }
    public function reservationRooms()   { return $this->hasMany(ReservationRoom::class); }
    public function channelRoomMappings(){ return $this->hasMany(ChannelRoomMapping::class); }
    public function allotments()         { return $this->hasMany(Allotment::class); }
    public function groupBlockRooms()    { return $this->hasMany(GroupBlockRoom::class); }
    public function waitlistEntries()    { return $this->hasMany(WaitlistEntry::class, 'preferred_room_type_id'); }
    public function rateOverrides()      { return $this->hasMany(RateOverride::class); }
    public function dynamicPricingRules(){ return $this->hasMany(DynamicPricingRule::class); }
    public function parityAlerts()       { return $this->hasMany(ChannelParityAlert::class); }
}
