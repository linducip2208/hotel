<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'modifiers' => 'array',
        'sent_to_kitchen' => 'boolean',
        'sent_at' => 'datetime',
        'is_void' => 'boolean',
    ];

    public function order() { return $this->belongsTo(PosOrder::class, 'order_id'); }
    public function menuItem() { return $this->belongsTo(PosMenuItem::class, 'menu_item_id'); }
}
