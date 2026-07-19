<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_accessible' => 'boolean',
        'min_spend' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function outlet() { return $this->belongsTo(PosOutlet::class); }
    public function reservations() { return $this->hasMany(TableReservation::class); }
}
