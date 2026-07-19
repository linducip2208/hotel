<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function webhook() { return $this->belongsTo(Webhook::class); }
}
