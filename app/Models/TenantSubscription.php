<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'trial_ends_at' => 'date',
        'cancelled_at' => 'datetime',
        'price_paid_idr' => 'decimal:2',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function plan()   { return $this->belongsTo(Plan::class); }
    public function invoices(){ return $this->hasMany(TenantInvoice::class, 'subscription_id'); }
}
