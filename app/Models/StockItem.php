<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'current_qty' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'average_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }
    public function recipes()   { return $this->hasMany(PosRecipe::class, 'stock_item_id'); }
}
