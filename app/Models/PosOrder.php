<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
    ];

    public function outlet()      { return $this->belongsTo(PosOutlet::class, 'outlet_id'); }
    public function property()    { return $this->belongsTo(Property::class); }
    public function table()       { return $this->belongsTo(PosTable::class, 'table_id'); }
    public function items()       { return $this->hasMany(PosOrderItem::class, 'order_id'); }
    public function payments()    { return $this->hasMany(PosOrderPayment::class, 'order_id'); }
    public function folio()       { return $this->belongsTo(Folio::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function server()      { return $this->belongsTo(User::class, 'server_id'); }
}
