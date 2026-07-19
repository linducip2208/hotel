<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'property_id'     => $this->property_id,
            'user_id'         => $this->user_id,
            'employee_number' => $this->employee_number,
            'name'            => $this->full_name,
            'position'        => $this->position,
            'department'      => $this->department,
            'hire_date'       => $this->joined_at?->toIso8601String(),
            'salary'          => isset($this->basic_salary) ? number_format((float) $this->basic_salary, 2, '.', '') : null,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),

            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'email' => $this->user->email,
            ]),
        ];
    }
}
