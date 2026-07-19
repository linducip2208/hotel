<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'roles'         => $this->when($this->relationLoaded('roles'), $this->getRoleNames()),
            'department'    => $this->department,
            'is_active'     => $this->is_active,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),

            'permissions' => $this->when($this->relationLoaded('permissions'), fn () => $this->getAllPermissions()->pluck('name')),
        ];
    }
}
