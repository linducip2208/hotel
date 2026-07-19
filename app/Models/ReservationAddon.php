<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationAddon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date_apply' => 'date',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function reservation() { return $this->belongsTo(Reservation::class); }
}
