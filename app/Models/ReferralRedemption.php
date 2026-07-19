<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralRedemption extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['discount_applied' => 'decimal:2', 'reward_credited' => 'decimal:2'];

    public function referralCode(){ return $this->belongsTo(ReferralCode::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
