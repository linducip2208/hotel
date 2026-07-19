<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class FolioChargeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'folio_id'              => $this->folio_id,
            'description'           => $this->description,
            'amount'                => isset($this->amount) ? number_format((float) $this->amount, 2, '.', '') : null,
            'quantity'              => $this->quantity,
            'unit_price'            => isset($this->unit_price) ? number_format((float) $this->unit_price, 2, '.', '') : null,
            'account_code'          => $this->account_code,
            'charge_date'           => $this->charge_date?->toIso8601String(),
            'is_taxable'            => $this->is_taxable,
            'tax_amount'            => isset($this->tax_amount) ? number_format((float) $this->tax_amount, 2, '.', '') : null,
            'service_charge_amount' => isset($this->service_charge_amount) ? number_format((float) $this->service_charge_amount, 2, '.', '') : null,
            'created_at'            => $this->created_at?->toIso8601String(),
        ];
    }
}
