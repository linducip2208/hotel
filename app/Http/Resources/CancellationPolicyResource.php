<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class CancellationPolicyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'name'            => $this->name,
            'description'     => $this->description,
            'deadline_hours'  => $this->deadline_hours,
            'penalty_percent' => $this->penalty_percent,
            'is_default'      => $this->is_default,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
