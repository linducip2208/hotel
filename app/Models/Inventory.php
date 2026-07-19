<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->total - $this->sold - $this->blocked - $this->out_of_order + $this->overbooking_allowance);
    }
}
