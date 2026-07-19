<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantInvoice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'issued_at' => 'date',
        'due_at' => 'date',
        'paid_at' => 'datetime',
        'line_items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function subscription() { return $this->belongsTo(TenantSubscription::class, 'subscription_id'); }
}
