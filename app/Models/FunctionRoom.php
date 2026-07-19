<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoom extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['amenities' => 'array', 'photos' => 'array', 'is_active' => 'boolean', 'half_day_rate' => 'decimal:2', 'full_day_rate' => 'decimal:2'];

    public function property() { return $this->belongsTo(Property::class); }
    public function events() { return $this->hasMany(Event::class); }
}
