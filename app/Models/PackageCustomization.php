<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageCustomization extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['price_modifier' => 'decimal:2'];

    public function reservationPackage() { return $this->belongsTo(ReservationPackage::class); }
    public function property() { return $this->belongsTo(Property::class); }
}
