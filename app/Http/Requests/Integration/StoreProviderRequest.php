<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'api_format' => ['required', 'string', 'max:50'],
            'base_url' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'extra_headers' => ['nullable', 'json'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
