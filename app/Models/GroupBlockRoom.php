<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupBlockRoom extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['rate' => 'decimal:2'];

    public function groupBlock() { return $this->belongsTo(GroupBlock::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
}
