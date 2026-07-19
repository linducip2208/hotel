<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class FolioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'reservation_id'  => $this->reservation_id,
            'property_id'     => $this->property_id,
            'folio_number'    => $this->folio_number,
            'status'          => $this->status,
            'total_charges'   => isset($this->total_charges) ? number_format((float) $this->total_charges, 2, '.', '') : null,
            'total_payments'  => isset($this->total_payments) ? number_format((float) $this->total_payments, 2, '.', '') : null,
            'total_discounts' => isset($this->total_discounts) ? number_format((float) $this->total_discounts, 2, '.', '') : null,
            'balance'         => isset($this->balance) ? number_format((float) $this->balance, 2, '.', '') : null,
            'settled_at'      => $this->closed_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),

            'charges'      => FolioChargeResource::collection($this->whenLoaded('charges')),
            'payments'     => FolioPaymentResource::collection($this->whenLoaded('payments')),
            'reservation'  => $this->whenLoaded('reservation', fn () => [
                'id'  => $this->reservation->id,
                'ref' => $this->reservation->ref,
            ]),
        ];
    }
}
