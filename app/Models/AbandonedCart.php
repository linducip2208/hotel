<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'cart_data' => 'array',
        'recovered_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
