<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ChannelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'api_format'   => $this->api_format,
            'is_active'    => $this->is_active,
            'last_sync_at' => $this->last_sync_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
