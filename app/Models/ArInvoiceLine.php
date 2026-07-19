<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArInvoiceLine extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function invoice() { return $this->belongsTo(ArInvoice::class, 'invoice_id'); }
}
