<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinibarStock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'initial_qty' => 'integer',
        'current_qty' => 'integer',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function product() { return $this->belongsTo(MinibarProduct::class, 'minibar_product_id'); }
}
