<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DripCampaign extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function steps()    { return $this->hasMany(DripStep::class)->orderBy('sort_order'); }
    public function queueItems() { return $this->hasManyThrough(DripQueue::class, DripStep::class); }
}
