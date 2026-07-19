<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folio extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'total_charges' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function property()     { return $this->belongsTo(Property::class); }
    public function reservation()  { return $this->belongsTo(Reservation::class); }
    public function guest()        { return $this->belongsTo(Guest::class); }
    public function company()      { return $this->belongsTo(Company::class); }
    public function cashier()      { return $this->belongsTo(User::class, 'cashier_id'); }
    public function charges()      { return $this->hasMany(FolioCharge::class); }
    public function payments()     { return $this->hasMany(FolioPayment::class); }
    public function posOrders()    { return $this->hasMany(PosOrder::class); }
    public function spaAppointments() { return $this->hasMany(SpaAppointment::class); }
    public function masterForGroupBlock() { return $this->hasOne(GroupBlock::class, 'master_folio_id'); }
    public function voucherRedemptions() { return $this->hasMany(VoucherRedemption::class); }
    public function vouchersPurchased()  { return $this->hasMany(GiftVoucher::class, 'purchased_via_folio_id'); }

    public function recalculate(): void
    {
        $this->total_charges = (float) $this->charges()->where('is_void', false)->sum('amount')
            + (float) $this->charges()->where('is_void', false)->sum('tax_amount');
        $this->total_payments = (float) $this->payments()->where('is_void', false)->sum('amount');
        $this->balance = $this->total_charges - $this->total_payments;
        $this->save();
    }
}
