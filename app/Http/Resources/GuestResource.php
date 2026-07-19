<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class GuestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'full_name'      => $this->full_name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'id_type'        => $this->id_type,
            'id_number'      => $this->id_number,
            'nationality'    => $this->nationality,
            'date_of_birth'  => $this->date_of_birth?->toIso8601String(),
            'address'        => $this->address,
            'city'           => $this->city,
            'postal_code'    => $this->postal_code,
            'npwp'           => $this->npwp,
            'vip'            => $this->is_vip,
            'preferences'    => $this->preferences,
            'stay_count'     => $this->stay_count,
            'total_revenue'  => isset($this->total_revenue) ? number_format((float) $this->total_revenue, 2, '.', '') : null,
            'last_stay_at'   => $this->last_stay_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),

            'reservations_count' => $this->whenLoaded('reservations', fn () => $this->reservations->count()),
            'folios_count'       => $this->whenLoaded('folios', fn () => $this->folios->count()),
        ];
    }
}
