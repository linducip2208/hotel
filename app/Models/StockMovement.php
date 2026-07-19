<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'qty' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'moved_at' => 'datetime',
    ];

    public function stockItem()   { return $this->belongsTo(StockItem::class); }
    public function performedBy() { return $this->belongsTo(User::class, 'performed_by_user_id'); }
}
