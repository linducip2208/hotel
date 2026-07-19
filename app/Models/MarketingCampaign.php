<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['audience_filter' => 'array', 'scheduled_at' => 'datetime'];

    public function property() { return $this->belongsTo(Property::class); }
    public function template() { return $this->belongsTo(MessageTemplate::class, 'template_id'); }
}
