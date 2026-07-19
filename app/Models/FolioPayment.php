<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'mdr_amount' => 'decimal:2',
        'is_void' => 'boolean',
        'gateway_payload' => 'array',
    ];

    public function folio()    { return $this->belongsTo(Folio::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function cashier()  { return $this->belongsTo(User::class, 'cashier_id'); }
    public function shift()    { return $this->belongsTo(CashierShift::class, 'shift_id'); }
    public function provider() { return $this->belongsTo(Provider::class); }
}
