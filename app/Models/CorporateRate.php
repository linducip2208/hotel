<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'negotiated_rate' => 'decimal:2',
        'blackout_dates' => 'array',
        'is_active' => 'boolean',
    ];

    public function property()          { return $this->belongsTo(Property::class); }
    public function corporateAccount()  { return $this->belongsTo(CorporateAccount::class); }
    public function roomType()          { return $this->belongsTo(RoomType::class); }
}
