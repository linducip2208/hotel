<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'issued_at' => 'date',
        'due_at' => 'date',
        'attachments' => 'array',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'withholding_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function supplier() { return $this->belongsTo(ApSupplier::class, 'supplier_id'); }
    public function lines() { return $this->hasMany(ApBillLine::class, 'bill_id'); }
    public function payments() { return $this->hasMany(ApPayment::class, 'bill_id'); }
}
