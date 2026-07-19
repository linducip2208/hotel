<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

final class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'rooms.*.rate_plan_id' => ['required', 'integer', 'exists:rate_plans,id'],
            'rooms.*.adults' => ['required', 'integer', 'min:1', 'max:10'],
            'rooms.*.children' => ['nullable', 'integer', 'min:0', 'max:10'],
            'primary_guest.first_name' => ['required', 'string', 'max:100'],
            'primary_guest.last_name' => ['nullable', 'string', 'max:100'],
            'primary_guest.email' => ['nullable', 'email', 'max:255'],
            'primary_guest.phone' => ['nullable', 'string', 'max:20'],
            'primary_guest.npwp' => ['nullable', 'string', 'max:20'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:50'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
