<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class HousekeepingRoomStatusResource extends JsonResource
{
    /**
     * Accepts a Room model with loaded hkTasks or a plain array.
     */
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'room_number'         => $this->room_number,
            'status'              => $this->status,
            'last_cleaned_at'     => $this->last_cleaned_at?->toIso8601String(),
            'current_task_status' => $this->current_task_status ?? null,
            'current_attendant'   => $this->current_attendant ?? null,
        ];
    }
}
