<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfmSegmentRule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'auto_actions' => 'array',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
