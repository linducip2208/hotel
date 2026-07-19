<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function outlet() { return $this->belongsTo(PosOutlet::class, 'outlet_id'); }
    public function menuItems() { return $this->hasMany(PosMenuItem::class, 'category_id'); }
}
