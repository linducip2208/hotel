<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

final class MoveRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_room_id' => ['required', 'integer', 'exists:rooms,id'],
            'to_room_id' => ['required', 'integer', 'exists:rooms,id', 'different:from_room_id'],
        ];
    }
}
