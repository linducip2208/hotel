<?php

declare(strict_types=1);

namespace App\Http\Requests\Housekeeping;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
        ];
    }
}
