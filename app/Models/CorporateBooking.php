<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateBooking extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rate_applied' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function property()          { return $this->belongsTo(Property::class); }
    public function corporateAccount()  { return $this->belongsTo(CorporateAccount::class); }
    public function reservation()       { return $this->belongsTo(Reservation::class); }
}
