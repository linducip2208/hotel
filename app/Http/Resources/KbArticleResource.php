<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class KbArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'property_id' => $this->property_id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'category'    => $this->category,
            'body'        => $this->content,
            'is_published'=> $this->is_published,
            'created_by'  => $this->author_user_id,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
