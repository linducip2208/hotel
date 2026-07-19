<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function items()
    {
        return $this->hasMany(PackageItem::class);
    }

    public function reservationPackages()
    {
        return $this->hasMany(ReservationPackage::class);
    }
}
