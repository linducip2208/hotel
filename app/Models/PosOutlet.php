<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOutlet extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'charge_to_room_enabled' => 'boolean',
        'takeaway_enabled' => 'boolean',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function tables() { return $this->hasMany(PosTable::class, 'outlet_id'); }
    public function categories() { return $this->hasMany(PosCategory::class, 'outlet_id'); }
    public function menuItems() { return $this->hasMany(PosMenuItem::class, 'outlet_id'); }
}
