<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'ref'                   => $this->ref,
            'property_id'           => $this->property_id,
            'status'                => $this->status,
            'check_in'              => $this->check_in?->toIso8601String(),
            'check_out'             => $this->check_out?->toIso8601String(),
            'arrival_time'          => $this->arrival_time?->toIso8601String(),
            'adults'                => $this->adults,
            'children'              => $this->children,
            'children_ages'         => $this->children_ages,
            'special_requests'      => $this->special_requests,
            'source'                => $this->source,
            'total_room'            => isset($this->total_room) ? number_format((float) $this->total_room, 2, '.', '') : null,
            'total_addons'          => isset($this->total_addons) ? number_format((float) $this->total_addons, 2, '.', '') : null,
            'service_charge'        => isset($this->service_charge) ? number_format((float) $this->service_charge, 2, '.', '') : null,
            'tax_total'             => isset($this->tax_total) ? number_format((float) $this->tax_total, 2, '.', '') : null,
            'grand_total'           => isset($this->grand_total) ? number_format((float) $this->grand_total, 2, '.', '') : null,
            'balance'               => isset($this->balance) ? number_format((float) $this->balance, 2, '.', '') : null,
            'discount_amount'       => isset($this->discount_amount) ? number_format((float) $this->discount_amount, 2, '.', '') : null,
            'cancellation_penalty'  => isset($this->cancellation_penalty) ? number_format((float) $this->cancellation_penalty, 2, '.', '') : null,
            'pre_checkin_complete'  => $this->pre_checkin_complete,
            'checked_in_at'         => $this->checked_in_at?->toIso8601String(),
            'checked_out_at'        => $this->checked_out_at?->toIso8601String(),
            'cancelled_at'          => $this->cancelled_at?->toIso8601String(),
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),

            'primary_guest' => new GuestResource($this->whenLoaded('primaryGuest')),
            'rooms'         => ReservationRoomResource::collection($this->whenLoaded('rooms')),
            'addons'        => ReservationAddonResource::collection($this->whenLoaded('addons')),
            'company'       => $this->whenLoaded('company', fn () => [
                'id'   => $this->company->id,
                'name' => $this->company->name,
            ]),
            'travel_agent'  => $this->whenLoaded('travelAgent', fn () => [
                'id'   => $this->travelAgent->id,
                'name' => $this->travelAgent->name,
            ]),
        ];
    }
}
