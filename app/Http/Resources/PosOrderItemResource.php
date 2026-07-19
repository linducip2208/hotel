<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PosOrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'menu_item_id' => $this->menu_item_id,
            'name'         => $this->name,
            'quantity'     => $this->quantity,
            'unit_price'   => isset($this->unit_price) ? number_format((float) $this->unit_price, 2, '.', '') : null,
            'subtotal'     => isset($this->subtotal) ? number_format((float) $this->subtotal, 2, '.', '') : null,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
