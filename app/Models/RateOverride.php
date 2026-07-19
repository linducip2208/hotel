<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateOverride extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'override_date'        => 'date',
        'price'                => 'decimal:2',
        'min_price'            => 'decimal:2',
        'max_price'            => 'decimal:2',
        'closed_to_arrival'    => 'boolean',
        'closed_to_departure'  => 'boolean',
        'stop_sell'            => 'boolean',
    ];

    public function property()      { return $this->belongsTo(Property::class); }
    public function roomType()      { return $this->belongsTo(RoomType::class); }
    public function ratePlan()      { return $this->belongsTo(RatePlan::class); }
    public function channel()       { return $this->belongsTo(Channel::class); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
