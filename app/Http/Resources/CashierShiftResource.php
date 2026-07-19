<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class CashierShiftResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'property_id'      => $this->property_id,
            'user_id'          => $this->cashier_id,
            'opened_at'        => $this->opened_at?->toIso8601String(),
            'closed_at'        => $this->closed_at?->toIso8601String(),
            'opening_balance'  => isset($this->opening_float) ? number_format((float) $this->opening_float, 2, '.', '') : null,
            'closing_balance'  => isset($this->closing_balance) ? number_format((float) $this->closing_balance, 2, '.', '') : null,
            'expected_cash'    => isset($this->expected_cash) ? number_format((float) $this->expected_cash, 2, '.', '') : null,
            'actual_cash'      => isset($this->actual_cash) ? number_format((float) $this->actual_cash, 2, '.', '') : null,
            'discrepancy'      => isset($this->cash_variance) ? number_format((float) $this->cash_variance, 2, '.', '') : null,
            'status'           => $this->status,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
