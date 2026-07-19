<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['required_date' => 'date'];

    public function property() { return $this->belongsTo(Property::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function lines() { return $this->hasMany(PurchaseRequestLine::class, 'pr_id'); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class, 'pr_id'); }
}
