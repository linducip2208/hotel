<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class AvailabilityResource extends JsonResource
{
    /**
     * Not tied to a single model. Accepts an array or object with availability data.
     */
    public function toArray($request): array
    {
        return [
            'room_type_id'    => $this->resource['room_type_id'] ?? $this->room_type_id ?? null,
            'room_type_name'  => $this->resource['room_type_name'] ?? $this->room_type_name ?? null,
            'date'            => isset($this->resource['date'])
                ? (is_string($this->resource['date']) ? $this->resource['date'] : $this->resource['date']->toIso8601String())
                : (isset($this->date) ? (is_string($this->date) ? $this->date : $this->date->toIso8601String()) : null),
            'available_rooms' => $this->resource['available_rooms'] ?? $this->available_rooms ?? 0,
            'total_rooms'     => $this->resource['total_rooms'] ?? $this->total_rooms ?? 0,
            'min_rate'        => isset($this->resource['min_rate'])
                ? number_format((float) $this->resource['min_rate'], 2, '.', '')
                : (isset($this->min_rate) ? number_format((float) $this->min_rate, 2, '.', '') : null),
            'status'          => $this->resource['status'] ?? $this->status ?? null,
        ];
    }
}
