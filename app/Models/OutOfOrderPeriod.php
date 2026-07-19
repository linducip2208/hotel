<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutOfOrderPeriod extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'cleared_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room()     { return $this->belongsTo(Room::class); }
    public function workOrder(){ return $this->belongsTo(WorkOrder::class); }
    public function createdByUser(){ return $this->belongsTo(User::class, 'created_by_user_id'); }
}
