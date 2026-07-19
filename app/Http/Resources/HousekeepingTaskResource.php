<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class HousekeepingTaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'property_id'   => $this->property_id,
            'room_id'       => $this->room_id,
            'assigned_to'   => $this->assignee_id,
            'task_type'     => $this->task_type,
            'status'        => $this->status,
            'priority'      => $this->priority,
            'notes'         => $this->notes,
            'completed_at'  => $this->completed_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),

            'room'     => $this->whenLoaded('room', fn () => [
                'room_number' => $this->room->room_number,
                'status'      => $this->room->status,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn () => [
                'id'   => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
        ];
    }
}
