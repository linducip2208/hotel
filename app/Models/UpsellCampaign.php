<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsellCampaign extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'offer_ids' => 'array',
        'guest_filters' => 'array',
        'revenue_generated' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function logs() { return $this->hasMany(UpsellCampaignLog::class); }
}
