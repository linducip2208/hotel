<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelAgent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'default_commission_pct' => 'decimal:3',
        'credit_limit' => 'decimal:2',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservations(){ return $this->hasMany(Reservation::class); }
    public function arAccounts()  { return $this->hasMany(ArAccount::class); }
    public function allotments()  { return $this->hasMany(Allotment::class); }
}
