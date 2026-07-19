<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsellOffer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'min_stay_nights' => 'integer',
        'days_before_arrival' => 'integer',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function upgradeToRoomType() { return $this->belongsTo(RoomType::class, 'upgrade_to_room_type_id'); }
    public function presentations() { return $this->hasMany(UpsellPresentation::class); }
}
