<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function menuRecipe() { return $this->belongsTo(MenuRecipe::class); }
}
