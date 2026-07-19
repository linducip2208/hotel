<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApBillLine extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'withholding_amount' => 'decimal:2',
    ];

    public function bill() { return $this->belongsTo(ApBill::class, 'bill_id'); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
