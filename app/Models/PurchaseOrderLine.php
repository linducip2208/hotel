<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'total' => 'decimal:2'];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function stockItem() { return $this->belongsTo(StockItem::class); }
}
