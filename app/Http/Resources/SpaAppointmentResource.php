<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SpaAppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'property_id'      => $this->property_id,
            'reservation_id'   => $this->reservation_id,
            'guest_id'         => $this->guest_id,
            'treatment_id'     => $this->treatment_id,
            'therapist_id'     => $this->therapist_id,
            'cabin_id'         => $this->cabin_id,
            'start_time'       => $this->start_at?->toIso8601String(),
            'end_time'         => $this->end_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'status'           => $this->status,
            'price'            => isset($this->price) ? number_format((float) $this->price, 2, '.', '') : null,
            'created_at'       => $this->created_at?->toIso8601String(),

            'treatment' => $this->whenLoaded('treatment', fn () => [
                'id'   => $this->treatment->id,
                'name' => $this->treatment->name,
            ]),
            'therapist' => $this->whenLoaded('therapist', fn () => [
                'id'   => $this->therapist->id,
                'name' => $this->therapist->name,
            ]),
            'guest'     => $this->whenLoaded('guest', fn () => [
                'id'   => $this->guest->id,
                'name' => $this->guest->full_name,
            ]),
        ];
    }
}
