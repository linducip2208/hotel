<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ReservationRoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'reservation_id'  => $this->reservation_id,
            'room_id'         => $this->room_id,
            'room_type_id'    => $this->room_type_id,
            'rate_plan_id'    => $this->rate_plan_id,
            'adults'          => $this->adults,
            'children'        => $this->children,
            'rate_per_night'  => $this->per_night_rates,
            'total'           => isset($this->subtotal) ? number_format((float) $this->subtotal, 2, '.', '') : null,
            'status'          => $this->status,
            'created_at'      => $this->created_at?->toIso8601String(),

            'room'      => new RoomResource($this->whenLoaded('room')),
            'room_type' => new RoomTypeResource($this->whenLoaded('roomType')),
        ];
    }
}
