<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherRedemption extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'decimal:2', 'redeemed_at' => 'datetime'];

    public function voucher()    { return $this->belongsTo(GiftVoucher::class, 'voucher_id'); }
    public function folio()      { return $this->belongsTo(Folio::class); }
    public function reservation(){ return $this->belongsTo(Reservation::class); }
    public function redeemedBy() { return $this->belongsTo(User::class, 'redeemed_by_user_id'); }
}
