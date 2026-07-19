<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_float' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'cash_variance' => 'decimal:2',
        'breakdown' => 'array',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function cashier()   { return $this->belongsTo(User::class, 'cashier_id'); }
    public function payments()  { return $this->hasMany(FolioPayment::class, 'shift_id'); }
}
