<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'daily_rate' => 'decimal:2',
        'total_charge' => 'decimal:2',
        'is_valet' => 'boolean',
        'valet_key_location' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function parkingSlot() { return $this->belongsTo(ParkingSlot::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function valetByUser() { return $this->belongsTo(User::class, 'valet_by_user_id'); }
    public function folioCharge() { return $this->belongsTo(FolioCharge::class); }
}
