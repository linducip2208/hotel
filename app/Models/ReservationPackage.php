<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationPackage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price_charged' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function folioCharge()
    {
        return $this->belongsTo(FolioCharge::class);
    }
}
