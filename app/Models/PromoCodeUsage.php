<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    public function promoCode()   { return $this->belongsTo(PromoCode::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
    public function property()    { return $this->belongsTo(Property::class); }
}
