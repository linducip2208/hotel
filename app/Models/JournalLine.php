<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function entry() { return $this->belongsTo(JournalEntry::class, 'journal_entry_id'); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
