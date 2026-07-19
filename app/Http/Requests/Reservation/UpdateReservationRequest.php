<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'notes_internal' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
