<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PosMenuItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'outlet_id'    => $this->outlet_id,
            'category_id'  => $this->category_id,
            'name'         => $this->name,
            'sku'          => $this->sku,
            'price'        => isset($this->price) ? number_format((float) $this->price, 2, '.', '') : null,
            'tax_category' => $this->tax_category,
            'is_available' => $this->is_available,
            'image_url'    => $this->image_url,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
