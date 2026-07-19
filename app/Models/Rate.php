<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
        'cta' => 'boolean',
        'ctd' => 'boolean',
        'closed' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
    public function ratePlan() { return $this->belongsTo(RatePlan::class); }
}
