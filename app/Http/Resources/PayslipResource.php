<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class PayslipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'employee_id'   => $this->employee_id,
            'period_start'  => $this->period_start,
            'period_end'    => $this->period_end,
            'basic_salary'  => isset($this->basic_salary) ? number_format((float) $this->basic_salary, 2, '.', '') : null,
            'allowances'    => isset($this->allowances_total) ? number_format((float) $this->allowances_total, 2, '.', '') : null,
            'deductions'    => isset($this->deductions_total) ? number_format((float) $this->deductions_total, 2, '.', '') : null,
            'net_pay'       => isset($this->net_salary) ? number_format((float) $this->net_salary, 2, '.', '') : null,
            'status'        => $this->status,
            'generated_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
