<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateAccount extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'discount_pct' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'annual_room_night_commitment' => 'integer',
        'actual_room_nights' => 'integer',
    ];

    public function property()          { return $this->belongsTo(Property::class); }
    public function rates()             { return $this->hasMany(CorporateRate::class); }
    public function bookings()          { return $this->hasMany(CorporateBooking::class); }
    public function reservations()      { return $this->hasManyThrough(Reservation::class, CorporateBooking::class, 'corporate_account_id', 'id', 'id', 'reservation_id'); }

    public function nightCommitmentPct(): float
    {
        if ($this->annual_room_night_commitment <= 0) return 0;
        return round(($this->actual_room_nights / $this->annual_room_night_commitment) * 100, 1);
    }

    public function totalRevenue(): float
    {
        return (float) $this->bookings()->sum('rate_applied');
    }

    public function totalDiscount(): float
    {
        return (float) $this->bookings()->sum('discount_amount');
    }

    public function creditUtilizationPct(): float
    {
        if ($this->credit_limit <= 0) return 0;
        $totalCharged = $this->reservations()->whereHas('folios', fn($q) => $q->where('balance', '>', 0))->count();
        return 0; // simplified — override with actual AR logic
    }
}
