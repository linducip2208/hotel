<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class RateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'rate_plan_id' => $this->rate_plan_id,
            'room_type_id' => $this->room_type_id,
            'date'         => $this->date?->toIso8601String(),
            'amount'       => isset($this->amount) ? number_format((float) $this->amount, 2, '.', '') : null,
            'currency'     => $this->currency,
            'is_active'    => $this->is_active,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
