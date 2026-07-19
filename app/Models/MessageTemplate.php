<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['variables' => 'array', 'is_active' => 'boolean'];

    public function property() { return $this->belongsTo(Property::class); }
}
