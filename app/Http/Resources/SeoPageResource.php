<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SeoPageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'url_slug'        => $this->url_slug,
            'title'           => $this->title,
            'meta_description'=> $this->meta_description,
            'h1'              => $this->h1,
            'content'         => $this->when($this->content, fn () => \mb_substr($this->content, 0, 300)),
            'schema_type'     => $this->schema_type,
            'is_indexable'    => $this->is_indexable,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
