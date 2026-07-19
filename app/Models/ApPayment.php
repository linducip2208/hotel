<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bill()         { return $this->belongsTo(ApBill::class, 'bill_id'); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
}
