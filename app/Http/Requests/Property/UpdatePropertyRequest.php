<?php

declare(strict_types=1);

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['sometimes', 'email', 'max:255'],
            'timezone' => ['sometimes', 'string', 'max:50'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'check_in_time' => ['sometimes', 'date_format:H:i'],
            'check_out_time' => ['sometimes', 'date_format:H:i'],
        ];
    }
}
