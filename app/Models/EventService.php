<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventService extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'cost' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function eventBooking() { return $this->belongsTo(EventBooking::class); }
}
