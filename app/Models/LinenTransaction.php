<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinenTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function property() { return $this->belongsTo(Property::class); }
    public function linenItem() { return $this->belongsTo(LinenItem::class, 'linen_item_id'); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
}
