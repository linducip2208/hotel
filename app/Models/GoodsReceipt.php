<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['received_date' => 'date'];

    public function property() { return $this->belongsTo(Property::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
    public function lines() { return $this->hasMany(GoodsReceiptLine::class, 'gr_id'); }
}
