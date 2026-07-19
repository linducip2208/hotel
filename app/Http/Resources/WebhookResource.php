<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class WebhookResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'url'              => $this->url,
            'events'           => $this->events,
            'is_active'        => $this->is_active,
            'last_delivery_at' => $this->last_delivered_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
