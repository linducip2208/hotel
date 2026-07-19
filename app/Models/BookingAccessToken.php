<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAccessToken extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function reservation() { return $this->belongsTo(Reservation::class); }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
