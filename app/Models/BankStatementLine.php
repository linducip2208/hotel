<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatementLine extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    public function statement()      { return $this->belongsTo(BankStatement::class, 'statement_id'); }
    public function matchedJournal() { return $this->belongsTo(JournalLine::class, 'matched_journal_line_id'); }
}
