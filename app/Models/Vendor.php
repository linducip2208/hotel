<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function contracts() { return $this->hasMany(VendorContract::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class, 'vendor_id'); }
    public function pmSchedules() { return $this->hasMany(PmSchedule::class, 'assigned_vendor_id'); }
}
