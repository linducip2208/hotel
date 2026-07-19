<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelConflict extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function property()        { return $this->belongsTo(Property::class); }
    public function channel()         { return $this->belongsTo(Channel::class); }
    public function resolvedByUser()  { return $this->belongsTo(User::class, 'resolved_by_user_id'); }
}
