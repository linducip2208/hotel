<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'property_id'   => $this->property_id,
            'room_type_id'  => $this->room_type_id,
            'room_number'   => $this->room_number,
            'floor'         => $this->floor,
            'status'        => $this->status,
            'notes'         => $this->notes,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),

            'room_type'           => new RoomTypeResource($this->whenLoaded('roomType')),
            'current_reservation' => $this->whenLoaded('reservationRooms', function () {
                $active = $this->reservationRooms
                    ->filter(fn ($rr) => $rr->reservation && in_array($rr->reservation->status, ['confirmed', 'checked_in']))
                    ->first();
                if (! $active) {
                    return null;
                }
                return [
                    'reservation_id' => $active->reservation_id,
                    'ref'            => $active->reservation?->ref,
                    'check_in'       => $active->check_in?->toIso8601String(),
                    'check_out'      => $active->check_out?->toIso8601String(),
                    'guest_name'     => $active->reservation?->primaryGuest?->full_name,
                ];
            }),
        ];
    }
}
