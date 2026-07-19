<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class StockMovementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'stock_item_id' => $this->stock_item_id,
            'type'          => $this->type,
            'quantity'      => $this->qty,
            'reference'     => $this->reference,
            'notes'         => $this->notes,
            'created_by'    => $this->performed_by_user_id,
            'created_at'    => $this->moved_at?->toIso8601String() ?? $this->created_at?->toIso8601String(),
        ];
    }
}
