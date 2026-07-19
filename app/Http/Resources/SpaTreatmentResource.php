<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SpaTreatmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'property_id'      => $this->property_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'price'            => isset($this->price) ? number_format((float) $this->price, 2, '.', '') : null,
            'category'         => $this->category,
            'is_active'        => $this->is_active,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
