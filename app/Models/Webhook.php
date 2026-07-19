<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_delivered_at' => 'datetime',
        'secret_encrypted' => 'encrypted',
    ];

    protected $hidden = ['secret_encrypted'];

    public function property()  { return $this->belongsTo(Property::class); }
    public function deliveries() { return $this->hasMany(WebhookDelivery::class); }
}
