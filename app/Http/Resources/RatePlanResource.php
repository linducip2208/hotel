<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class RatePlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                      => $this->id,
            'property_id'             => $this->property_id,
            'name'                    => $this->name,
            'slug'                    => $this->slug,
            'description'             => $this->description,
            'meal_plan'               => $this->meal_plan,
            'guarantee_type'          => $this->guarantee_type,
            'cancellation_policy_id'  => $this->cancellation_policy_id,
            'is_refundable'           => $this->is_refundable,
            'is_active'               => $this->is_active,
            'created_at'              => $this->created_at?->toIso8601String(),
            'updated_at'              => $this->updated_at?->toIso8601String(),

            'rates' => RateResource::collection($this->whenLoaded('rates')),
        ];
    }
}
