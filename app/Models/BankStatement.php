<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'statement_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function bankAccount(){ return $this->belongsTo(BankAccount::class); }
    public function lines()      { return $this->hasMany(BankStatementLine::class, 'statement_id'); }
}
