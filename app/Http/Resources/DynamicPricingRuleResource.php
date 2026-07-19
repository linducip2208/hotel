<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class DynamicPricingRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'name'            => $this->name,
            'rule_type'       => $this->rule_type,
            'conditions'      => [
                'threshold_low'  => isset($this->threshold_low) ? number_format((float) $this->threshold_low, 2, '.', '') : null,
                'threshold_high' => isset($this->threshold_high) ? number_format((float) $this->threshold_high, 2, '.', '') : null,
            ],
            'actions'         => [
                'action_value'      => isset($this->action_value) ? number_format((float) $this->action_value, 2, '.', '') : null,
                'min_price_floor'   => isset($this->min_price_floor) ? number_format((float) $this->min_price_floor, 2, '.', '') : null,
                'max_price_ceiling' => isset($this->max_price_ceiling) ? number_format((float) $this->max_price_ceiling, 2, '.', '') : null,
            ],
            'is_active'       => $this->is_active,
            'priority'        => $this->priority,
            'last_applied_at' => $this->last_applied_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
