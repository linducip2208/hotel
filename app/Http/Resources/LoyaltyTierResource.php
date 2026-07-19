<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class LoyaltyTierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'min_points' => $this->min_points,
            'min_stays'  => $this->min_stays,
            'benefits'   => $this->benefits,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
