<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'total_lifetime_value'      => 'decimal:2',
        'avg_daily_rate'            => 'decimal:2',
        'avg_fnb_spend_per_stay'    => 'decimal:2',
        'avg_spa_spend_per_stay'    => 'decimal:2',
        'avg_ancillary_spend'       => 'decimal:2',
        'avg_review_score'          => 'decimal:2',
        'typically_books_breakfast' => 'boolean',
        'typically_uses_spa'        => 'boolean',
        'typically_uses_fnb'        => 'boolean',
        'last_built_at'             => 'datetime',
    ];

    public function guest()             { return $this->belongsTo(Guest::class); }
    public function preferredRoomType() { return $this->belongsTo(RoomType::class, 'preferred_room_type_id'); }

    public function isHighValue(): bool
    {
        return $this->loyalty_score >= 70 || (float) $this->total_lifetime_value >= 10_000_000;
    }

    public function isAtRisk(): bool
    {
        return $this->churn_risk_score >= 70;
    }

    public function upsellTier(): string
    {
        return match (true) {
            $this->upsell_score >= 80 => 'hot',
            $this->upsell_score >= 50 => 'warm',
            default                   => 'cold',
        };
    }
}
