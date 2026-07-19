<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

final class ResolveConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:accept_channel,keep_pms,merge'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
