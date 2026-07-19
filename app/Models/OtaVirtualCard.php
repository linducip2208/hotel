<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtaVirtualCard extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'expires_on' => 'date',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'amount_authorized' => 'decimal:2',
        'amount_charged' => 'decimal:2',
        'charge_attempts' => 'array',
        'card_number_encrypted' => 'encrypted',
        'cvv_encrypted' => 'encrypted',
    ];

    protected $hidden = ['card_number_encrypted', 'cvv_encrypted'];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function channel()     { return $this->belongsTo(Channel::class); }
}
