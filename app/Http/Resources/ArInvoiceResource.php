<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ArInvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'property_id'   => $this->property_id,
            'company_id'    => $this->ar_account_id,
            'guest_id'      => $this->guest_id,
            'invoice_number'=> $this->invoice_number,
            'date'          => $this->issued_at?->toIso8601String(),
            'due_date'      => $this->due_at?->toIso8601String(),
            'total'         => isset($this->grand_total) ? number_format((float) $this->grand_total, 2, '.', '') : null,
            'balance'       => isset($this->balance) ? number_format((float) $this->balance, 2, '.', '') : null,
            'status'        => $this->status,
            'created_at'    => $this->created_at?->toIso8601String(),

            'lines'   => ArInvoiceLineResource::collection($this->whenLoaded('lines')),
            'company' => $this->whenLoaded('arAccount', fn () => [
                'id'   => $this->arAccount->id,
                'name' => $this->arAccount->name,
            ]),
        ];
    }
}
