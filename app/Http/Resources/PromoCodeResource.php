<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PromoCodeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'type'                => $this->type,
            'value'               => isset($this->discount_value) ? number_format((float) $this->discount_value, 2, '.', '') : null,
            'min_stay_nights'     => $this->min_stay_nights,
            'min_booking_amount'  => isset($this->min_booking_amount) ? number_format((float) $this->min_booking_amount, 2, '.', '') : null,
            'max_uses'            => $this->max_uses,
            'used_count'          => $this->used_count ?? $this->usages()->count(),
            'valid_from'          => $this->valid_from?->toIso8601String(),
            'valid_to'            => $this->valid_until?->toIso8601String(),
            'is_active'           => $this->is_active,
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
