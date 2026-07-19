<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class GuestProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'guest_id'            => $this->guest_id,
            'preferences'         => $this->preferences,
            'segments'            => $this->segments,
            'lifetime_value'      => isset($this->total_lifetime_value) ? number_format((float) $this->total_lifetime_value, 2, '.', '') : null,
            'avg_stay_days'       => $this->avg_stay_days,
            'favorite_room_types' => $this->favorite_room_types,
            'complaint_count'     => $this->complaint_count,
            'last_nps_score'      => $this->last_nps_score,
            'data'                => $this->data ?? [
                'loyalty_score'            => $this->loyalty_score,
                'upsell_score'             => $this->upsell_score,
                'churn_risk_score'         => $this->churn_risk_score,
                'avg_daily_rate'           => isset($this->avg_daily_rate) ? number_format((float) $this->avg_daily_rate, 2, '.', '') : null,
                'avg_fnb_spend_per_stay'   => isset($this->avg_fnb_spend_per_stay) ? number_format((float) $this->avg_fnb_spend_per_stay, 2, '.', '') : null,
                'avg_spa_spend_per_stay'   => isset($this->avg_spa_spend_per_stay) ? number_format((float) $this->avg_spa_spend_per_stay, 2, '.', '') : null,
                'avg_ancillary_spend'      => isset($this->avg_ancillary_spend) ? number_format((float) $this->avg_ancillary_spend, 2, '.', '') : null,
                'avg_review_score'         => isset($this->avg_review_score) ? number_format((float) $this->avg_review_score, 2, '.', '') : null,
                'typically_books_breakfast'=> $this->typically_books_breakfast,
                'typically_uses_spa'       => $this->typically_uses_spa,
                'typically_uses_fnb'       => $this->typically_uses_fnb,
            ],
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
