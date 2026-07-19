<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class AssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'name'            => $this->name,
            'category'        => $this->category,
            'serial_number'   => $this->serial_number,
            'purchase_date'   => $this->purchased_at?->toIso8601String(),
            'purchase_cost'   => isset($this->purchase_cost) ? number_format((float) $this->purchase_cost, 2, '.', '') : null,
            'location'        => $this->location,
            'status'          => $this->status,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
