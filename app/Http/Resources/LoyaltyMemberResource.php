<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class LoyaltyMemberResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'guest_id'             => $this->guest_id,
            'member_number'        => $this->member_number,
            'tier_id'              => $this->tier_id,
            'points_balance'       => $this->points_balance,
            'total_points_earned'  => $this->total_points_earned,
            'enrolled_at'          => $this->enrolled_at?->toIso8601String(),
            'created_at'           => $this->created_at?->toIso8601String(),

            'guest' => $this->whenLoaded('guest', fn () => [
                'id'   => $this->guest->id,
                'name' => $this->guest->full_name,
            ]),
            'tier'  => $this->whenLoaded('tier', fn () => [
                'id'   => $this->tier->id,
                'name' => $this->tier->name,
            ]),
        ];
    }
}
