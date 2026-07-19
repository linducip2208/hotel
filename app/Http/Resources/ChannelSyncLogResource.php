<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ChannelSyncLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'channel_id'   => $this->channel_id,
            'sync_type'    => $this->sync_type,
            'status'       => $this->status,
            'records_sent' => $this->records_sent,
            'errors'       => $this->errors ?? $this->response_summary,
            'started_at'   => $this->started_at?->toIso8601String(),
            'completed_at' => $this->finished_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
