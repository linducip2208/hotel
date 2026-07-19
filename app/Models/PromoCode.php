<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function usages()   { return $this->hasMany(PromoCodeUsage::class); }
}
