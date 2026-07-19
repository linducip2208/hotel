<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class RoomTypeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'property_id' => $this->property_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'max_adults'  => $this->max_adults,
            'max_children'=> $this->max_children,
            'base_price'  => isset($this->base_rate) ? number_format((float) $this->base_rate, 2, '.', '') : null,
            'size_sqm'    => $this->size_sqm,
            'bed_type'    => $this->bed_type,
            'amenities'   => $this->amenities,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
