<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class BanquetEventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'name'            => $this->name,
            'event_type'      => $this->event_type,
            'function_room_id'=> $this->function_room_id,
            'date'            => $this->event_date?->toIso8601String(),
            'start_time'      => $this->start_time?->format('H:i'),
            'end_time'        => $this->end_time?->format('H:i'),
            'guests_count'    => $this->guests_count,
            'status'          => $this->status,
            'total'           => isset($this->grand_total) ? number_format((float) $this->grand_total, 2, '.', '') : null,
            'created_at'      => $this->created_at?->toIso8601String(),

            'function_room' => $this->whenLoaded('functionRoom', fn () => [
                'id'   => $this->functionRoom->id,
                'name' => $this->functionRoom->name,
            ]),
            'menu_items' => $this->whenLoaded('menuItems', fn () => $this->menuItems->map(fn ($item) => [
                'id'          => $item->id,
                'name'        => $item->name,
                'quantity'    => $item->quantity,
                'unit_price'  => isset($item->unit_price) ? number_format((float) $item->unit_price, 2, '.', '') : null,
                'subtotal'    => isset($item->subtotal) ? number_format((float) $item->subtotal, 2, '.', '') : null,
            ])),
        ];
    }
}
