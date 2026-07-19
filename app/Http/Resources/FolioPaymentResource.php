<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class FolioPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'folio_id'        => $this->folio_id,
            'amount'          => isset($this->amount) ? number_format((float) $this->amount, 2, '.', '') : null,
            'payment_method'  => $this->payment_method,
            'reference_number'=> $this->reference_number,
            'notes'           => $this->notes,
            'payment_date'    => $this->payment_date?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
