<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'thread_id'   => $this->thread_id,
            'sender_type' => $this->sender_type,
            'sender_id'   => $this->sender_id,
            'body'        => $this->body,
            'direction'   => $this->direction,
            'channel'     => $this->channel,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
