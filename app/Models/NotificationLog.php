<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata'     => 'array',
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function property()   { return $this->belongsTo(Property::class); }
    public function notifiable() { return $this->morphTo(); }
}
