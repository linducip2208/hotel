<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class NotificationLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'notifiable_type'  => $this->notifiable_type,
            'notifiable_id'    => $this->notifiable_id,
            'channel'          => $this->channel,
            'subject'          => $this->subject,
            'status'           => $this->status,
            'sent_at'          => $this->sent_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
