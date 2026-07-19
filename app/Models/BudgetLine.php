<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetLine extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'decimal:2'];

    public function period() { return $this->belongsTo(BudgetPeriod::class, 'budget_period_id'); }
    public function account(){ return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
