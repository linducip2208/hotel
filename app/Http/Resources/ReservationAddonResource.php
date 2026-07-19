<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ReservationAddonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'reservation_id'  => $this->reservation_id,
            'name'            => $this->name,
            'description'     => $this->description,
            'date_apply'      => $this->date_apply?->toIso8601String(),
            'unit_price'      => isset($this->unit_price) ? number_format((float) $this->unit_price, 2, '.', '') : null,
            'quantity'        => $this->quantity,
            'subtotal'        => isset($this->subtotal) ? number_format((float) $this->subtotal, 2, '.', '') : null,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
