<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class JournalEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'property_id' => $this->property_id,
            'date'        => $this->date,
            'reference'   => $this->reference,
            'description' => $this->description,
            'is_posted'   => $this->is_posted ?? ($this->posted_at !== null),
            'posted_at'   => $this->posted_at?->toIso8601String(),
            'created_by'  => $this->created_by_user_id,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),

            'lines' => JournalLineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
