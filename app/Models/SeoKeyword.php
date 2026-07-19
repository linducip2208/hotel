<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoKeyword extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
