<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class NightAuditResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'business_date'  => $this->audit_date?->toIso8601String(),
            'status'         => $this->status,
            'started_at'     => $this->started_at?->toIso8601String(),
            'completed_at'   => $this->completed_at?->toIso8601String(),
            'summary'        => $this->summary,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
