<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pb1Rate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:3',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];
}
