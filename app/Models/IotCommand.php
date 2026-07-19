<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotCommand extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function device() { return $this->belongsTo(IotDevice::class, 'iot_device_id'); }
    public function triggeredByUser() { return $this->belongsTo(User::class, 'triggered_by_user_id'); }
}
