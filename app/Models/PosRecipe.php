<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosRecipe extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['qty_per_serving' => 'decimal:4'];

    public function menuItem() { return $this->belongsTo(PosMenuItem::class, 'menu_item_id'); }
    public function stockItem(){ return $this->belongsTo(StockItem::class, 'stock_item_id'); }
}
