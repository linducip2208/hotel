<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class DocumentTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'property_id' => $this->property_id,
            'name'        => $this->name,
            'type'        => $this->type,
            'language'    => $this->language,
            'is_default'  => $this->is_default,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
