<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['order_date' => 'date', 'expected_date' => 'date', 'total' => 'decimal:2'];

    public function property() { return $this->belongsTo(Property::class); }
    public function vendor() { return $this->belongsTo(ApSupplier::class, 'vendor_id'); }
    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class, 'pr_id'); }
    public function orderedBy() { return $this->belongsTo(User::class, 'ordered_by'); }
    public function lines() { return $this->hasMany(PurchaseOrderLine::class, 'po_id'); }
    public function goodsReceipts() { return $this->hasMany(GoodsReceipt::class, 'po_id'); }
}
