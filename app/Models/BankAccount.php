<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'is_primary' => 'boolean'];

    public function property()  { return $this->belongsTo(Property::class); }
    public function coaAccount(){ return $this->belongsTo(ChartOfAccount::class, 'coa_account_id'); }
    public function statements(){ return $this->hasMany(BankStatement::class); }
}
