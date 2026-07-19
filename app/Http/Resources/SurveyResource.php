<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SurveyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'title'           => $this->title,
            'description'     => $this->description,
            'is_active'       => $this->is_active,
            'response_count'  => $this->response_count ?? ($this->relationLoaded('responses') ? $this->responses->count() : 0),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
