<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerStatement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'breakdown' => 'array',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'gross_revenue' => 'decimal:2',
        'mgmt_fee_pct' => 'decimal:3',
        'mgmt_fee_amount' => 'decimal:2',
        'expenses_total' => 'decimal:2',
        'net_payable_to_owner' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room()     { return $this->belongsTo(Room::class); }
}
