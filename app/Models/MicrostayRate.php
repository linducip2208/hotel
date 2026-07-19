<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicrostayRate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
}
