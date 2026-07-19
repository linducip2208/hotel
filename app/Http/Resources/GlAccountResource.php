<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class GlAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'code'      => $this->code,
            'name'      => $this->name,
            'type'      => $this->type,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'created_at'=> $this->created_at?->toIso8601String(),
            'updated_at'=> $this->updated_at?->toIso8601String(),
        ];
    }
}
