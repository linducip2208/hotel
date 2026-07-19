<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chargeback extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'chargeback_date' => 'date',
        'amount' => 'decimal:2',
        'evidence_deadline' => 'date',
        'response_submitted_at' => 'datetime',
        'final_decision_date' => 'date',
        'recovered_amount' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function folioCharge() { return $this->belongsTo(FolioCharge::class); }
    public function paymentTransaction() { return $this->belongsTo(FolioPayment::class, 'payment_transaction_id'); }
    public function evidence() { return $this->hasMany(ChargebackEvidence::class); }
}
