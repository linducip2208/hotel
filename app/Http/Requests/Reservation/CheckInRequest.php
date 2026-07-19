<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

final class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['required', 'integer', 'exists:rooms,id'],
        ];
    }
}
