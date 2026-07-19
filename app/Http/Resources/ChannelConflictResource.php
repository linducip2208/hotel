<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ChannelConflictResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'channel_id'     => $this->channel_id,
            'reservation_id' => $this->reservation_id,
            'conflict_type'  => $this->conflict_type,
            'channel_data'   => $this->details['channel_data'] ?? $this->details,
            'pms_data'       => $this->details['pms_data'] ?? [],
            'resolution'     => $this->resolution,
            'resolved_at'    => $this->resolved_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
