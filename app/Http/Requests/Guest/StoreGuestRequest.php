<?php

declare(strict_types=1);

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

final class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:guests,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'id_type' => ['nullable', 'string', 'in:ktp,passport,sim,kitas'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'size:2'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'npwp' => ['nullable', 'string', 'max:20'],
        ];
    }
}
