<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ArInvoiceLineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'invoice_id'   => $this->invoice_id,
            'description'  => $this->description,
            'quantity'     => $this->qty,
            'unit_price'   => isset($this->unit_price) ? number_format((float) $this->unit_price, 2, '.', '') : null,
            'amount'       => isset($this->amount) ? number_format((float) $this->amount, 2, '.', '') : null,
            'account_code' => $this->account_code,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
