<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsellPresentation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'offered_at' => 'datetime',
        'responded_at' => 'datetime',
        'price_offered' => 'decimal:2',
        'price_accepted' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function offer() { return $this->belongsTo(UpsellOffer::class, 'upsell_offer_id'); }
    public function acceptedByUser() { return $this->belongsTo(User::class, 'accepted_by_user_id'); }
}
