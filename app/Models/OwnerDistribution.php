<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerDistribution extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_revenue' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'distribution_amount' => 'decimal:2',
        'distribution_pct' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
