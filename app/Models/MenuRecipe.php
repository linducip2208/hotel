<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRecipe extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'selling_price' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function menuItem() { return $this->belongsTo(PosMenuItem::class); }
    public function ingredients() { return $this->hasMany(RecipeIngredient::class); }
    public function performances() { return $this->hasMany(MenuPerformance::class); }

    public function getFoodCostAttribute(): float
    {
        return (float) $this->ingredients()->sum('total_cost');
    }

    public function getFoodCostPctAttribute(): float
    {
        if ($this->selling_price <= 0) return 0;
        return round(($this->food_cost / $this->selling_price) * 100, 2);
    }

    public function getGrossProfitAttribute(): float
    {
        return $this->selling_price - $this->food_cost;
    }
}
