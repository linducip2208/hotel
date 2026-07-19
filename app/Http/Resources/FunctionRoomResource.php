<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class FunctionRoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'name'           => $this->name,
            'capacity'       => $this->capacity,
            'size_sqm'       => $this->size_sqm,
            'floor'          => $this->floor,
            'hourly_rate'    => isset($this->hourly_rate) ? number_format((float) $this->hourly_rate, 2, '.', '') : null,
            'half_day_rate'  => isset($this->half_day_rate) ? number_format((float) $this->half_day_rate, 2, '.', '') : null,
            'full_day_rate'  => isset($this->full_day_rate) ? number_format((float) $this->full_day_rate, 2, '.', '') : null,
            'is_active'      => $this->is_active,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
