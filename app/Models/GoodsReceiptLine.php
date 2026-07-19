<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['quantity_received' => 'decimal:3', 'quantity_accepted' => 'decimal:3'];

    public function goodsReceipt() { return $this->belongsTo(GoodsReceipt::class, 'gr_id'); }
    public function stockItem() { return $this->belongsTo(StockItem::class); }
}
