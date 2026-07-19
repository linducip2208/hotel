<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrderPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['amount' => 'decimal:2'];

    public function order() { return $this->belongsTo(PosOrder::class, 'order_id'); }
}
