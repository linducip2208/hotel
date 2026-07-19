<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance_cached' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function travelAgent() { return $this->belongsTo(TravelAgent::class); }
    public function channel() { return $this->belongsTo(Channel::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function invoices() { return $this->hasMany(ArInvoice::class); }
}
