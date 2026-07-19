<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'reservation_id' => $this->reservation_id,
            'guest_id'       => $this->guest_id,
            'rating'         => $this->rating,
            'title'          => $this->title,
            'body'           => $this->body,
            'is_published'   => $this->is_published,
            'replied_at'     => $this->replied_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),

            'guest' => $this->whenLoaded('guest', fn () => [
                'id'   => $this->guest->id,
                'name' => $this->guest->full_name,
            ]),
            'reply' => $this->when($this->reply, fn () => [
                'body'       => $this->reply,
                'created_at' => $this->replied_at?->toIso8601String(),
            ]),
        ];
    }
}
