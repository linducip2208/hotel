<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_smoking' => 'boolean',
        'is_accessible' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function property()     { return $this->belongsTo(Property::class); }
    public function roomType()     { return $this->belongsTo(RoomType::class); }
    public function hkTasks()      { return $this->hasMany(HkTask::class); }
    public function lostAndFound() { return $this->hasMany(LostAndFound::class); }
    public function assets()       { return $this->hasMany(Asset::class); }
    public function workOrders()   { return $this->hasMany(WorkOrder::class); }
    public function doorLockEvents(){ return $this->hasMany(DoorLockEvent::class); }
    public function reservationRooms() { return $this->hasMany(ReservationRoom::class); }
    public function outOfOrderPeriods() { return $this->hasMany(OutOfOrderPeriod::class); }
    public function guestRequests()    { return $this->hasMany(GuestRequest::class); }
    public function outOfOrderPeriods() { return $this->hasMany(OutOfOrderPeriod::class); }
    public function ownerStatements()  { return $this->hasMany(OwnerStatement::class); }
    public function iotDevices()       { return $this->hasMany(IotDevice::class); }
}
