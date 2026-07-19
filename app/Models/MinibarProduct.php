<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinibarProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function stocks() { return $this->hasMany(MinibarStock::class); }
    public function consumptions() { return $this->hasMany(MinibarConsumption::class); }
}
