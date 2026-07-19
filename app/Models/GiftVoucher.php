<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftVoucher extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'face_value' => 'decimal:2',
        'balance' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'issued_at' => 'datetime',
    ];

    public function property()      { return $this->belongsTo(Property::class); }
    public function issuedToGuest() { return $this->belongsTo(Guest::class, 'issued_to_guest_id'); }
    public function issuedByUser()  { return $this->belongsTo(User::class, 'issued_by_user_id'); }
    public function purchasedViaFolio() { return $this->belongsTo(Folio::class, 'purchased_via_folio_id'); }
    public function redemptions()   { return $this->hasMany(VoucherRedemption::class, 'voucher_id'); }
}
