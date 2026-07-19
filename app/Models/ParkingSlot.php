<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_vip' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function records() { return $this->hasMany(ParkingRecord::class); }
}
