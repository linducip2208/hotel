<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PosOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'outlet_id'       => $this->outlet_id,
            'table_id'        => $this->table_id,
            'order_number'    => $this->order_number,
            'status'          => $this->status,
            'subtotal'        => isset($this->subtotal) ? number_format((float) $this->subtotal, 2, '.', '') : null,
            'tax'             => isset($this->tax_total) ? number_format((float) $this->tax_total, 2, '.', '') : null,
            'service_charge'  => isset($this->service_charge) ? number_format((float) $this->service_charge, 2, '.', '') : null,
            'total'           => isset($this->grand_total) ? number_format((float) $this->grand_total, 2, '.', '') : null,
            'payment_method'  => $this->payment_method,
            'settled_at'      => $this->settled_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),

            'items'  => PosOrderItemResource::collection($this->whenLoaded('items')),
            'outlet' => $this->whenLoaded('outlet', fn () => [
                'id'   => $this->outlet->id,
                'name' => $this->outlet->name,
            ]),
            'table'  => $this->whenLoaded('table', fn () => [
                'id'   => $this->table->id,
                'name' => $this->table->name,
            ]),
        ];
    }
}
