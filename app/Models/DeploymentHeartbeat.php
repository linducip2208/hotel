<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeploymentHeartbeat extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'uptime_pct_24h' => 'decimal:2',
        'received_at'    => 'datetime',
        'created_at'     => 'datetime',
    ];
}
