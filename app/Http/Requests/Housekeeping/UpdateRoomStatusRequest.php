<?php

declare(strict_types=1);

namespace App\Http\Requests\Housekeeping;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRoomStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:clean,dirty,inspected,pickup,out_of_order,out_of_service'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
