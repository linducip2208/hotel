<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class StockItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'name'           => $this->name,
            'sku'            => $this->sku,
            'category'       => $this->category,
            'unit'           => $this->unit,
            'current_stock'  => $this->current_qty,
            'min_stock'      => $this->reorder_point,
            'max_stock'      => $this->max_stock,
            'unit_cost'      => isset($this->average_cost) ? number_format((float) $this->average_cost, 2, '.', '') : null,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
