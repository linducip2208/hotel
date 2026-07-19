<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPerformance extends Model
{
    use HasFactory;

    protected $table = 'menu_performance';

    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'units_sold' => 'integer',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'profit_margin_pct' => 'decimal:2',
        'popularity_pct' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function menuRecipe() { return $this->belongsTo(MenuRecipe::class); }
}
