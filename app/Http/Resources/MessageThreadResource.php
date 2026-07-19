<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class MessageThreadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'guest_id'        => $this->guest_id,
            'subject'         => $this->subject,
            'status'          => $this->status,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),

            'messages' => $this->whenLoaded('messages', fn () => MessageResource::collection(
                $this->messages->sortByDesc('created_at')->take(5)
            )),
        ];
    }
}
