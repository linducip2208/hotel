<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodWasteTarget extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'target_reduction_pct' => 'decimal:2',
        'baseline_kg' => 'decimal:3',
        'actual_kg' => 'decimal:3',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
