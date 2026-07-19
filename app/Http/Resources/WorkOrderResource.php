<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class WorkOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'property_id'   => $this->property_id,
            'asset_id'      => $this->asset_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'priority'      => $this->priority,
            'status'        => $this->status,
            'assigned_to'   => $this->assignee_id,
            'due_date'      => $this->due_date?->toIso8601String(),
            'completed_at'  => $this->completed_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
