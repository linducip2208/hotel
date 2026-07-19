<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PropertyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'address'        => $this->address,
            'city'           => $this->city,
            'state'          => $this->state,
            'country'        => $this->country,
            'postal_code'    => $this->postal_code,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'timezone'       => $this->timezone,
            'currency'       => $this->currency,
            'check_in_time'  => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'logo_url'       => $this->logo_url,
            'is_active'      => $this->is_active,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),

            'room_types' => RoomTypeResource::collection($this->whenLoaded('roomTypes')),
            'rooms_count' => $this->whenLoaded('rooms', fn () => $this->rooms->count()),
        ];
    }
}
