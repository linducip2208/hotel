<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class GiftVoucherResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'property_id' => $this->property_id,
            'code'        => $this->code,
            'amount'      => isset($this->face_value) ? number_format((float) $this->face_value, 2, '.', '') : null,
            'currency'    => $this->currency,
            'balance'     => isset($this->balance) ? number_format((float) $this->balance, 2, '.', '') : null,
            'valid_from'  => $this->valid_from?->toIso8601String(),
            'valid_to'    => $this->valid_until?->toIso8601String(),
            'is_active'   => $this->is_active,
            'issued_to'   => $this->issued_to ?? $this->issued_to_guest_id,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
