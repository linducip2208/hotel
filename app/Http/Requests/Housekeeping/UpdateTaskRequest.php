<?php

declare(strict_types=1);

namespace App\Http\Requests\Housekeeping;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
