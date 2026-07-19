<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ApprovalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'approvable_type'  => $this->approvable_type,
            'approvable_id'    => $this->approvable_id,
            'requested_by'     => $this->requester_id,
            'status'           => $this->status,
            'comments'         => $this->comments,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
