<?php

declare(strict_types=1);

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;

final class WizardPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
