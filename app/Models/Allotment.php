<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allotment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'release_date' => 'date',
        'negotiated_rate' => 'decimal:2',
    ];

    public function property()   { return $this->belongsTo(Property::class); }
    public function travelAgent(){ return $this->belongsTo(TravelAgent::class); }
    public function company()    { return $this->belongsTo(Company::class); }
    public function roomType()   { return $this->belongsTo(RoomType::class); }
    public function ratePlan()   { return $this->belongsTo(RatePlan::class); }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->rooms_blocked - $this->rooms_picked_up);
    }
}
