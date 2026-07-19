<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMenuItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function event() { return $this->belongsTo(Event::class); }
}
