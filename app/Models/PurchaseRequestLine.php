<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLine extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['quantity' => 'decimal:3', 'estimated_price' => 'decimal:2'];

    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class, 'pr_id'); }
    public function stockItem() { return $this->belongsTo(StockItem::class); }
}
