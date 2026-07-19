<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiIdempotencyKey extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'response'   => 'array',
        'expires_at' => 'datetime',
    ];
}
