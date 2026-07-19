<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ChannelParityAlertResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'channel_id'     => $this->channel_id,
            'room_type_id'   => $this->room_type_id,
            'rate_plan_id'   => $this->rate_plan_id,
            'date'           => $this->check_date?->toIso8601String(),
            'pms_rate'       => isset($this->direct_rate) ? number_format((float) $this->direct_rate, 2, '.', '') : null,
            'channel_rate'   => isset($this->channel_rate) ? number_format((float) $this->channel_rate, 2, '.', '') : null,
            'variance_pct'   => isset($this->gap_pct) ? number_format((float) $this->gap_pct, 4, '.', '') : null,
            'status'         => $this->status ?? $this->severity,
            'acknowledged_at'=> $this->acknowledged_at?->toIso8601String(),
            'resolved_at'    => $this->resolved_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
