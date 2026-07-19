<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rates' => ['required', 'array', 'min:1'],
            'rates.*.room_type_id' => ['required', 'integer'],
            'rates.*.rate_plan_id' => ['required', 'integer'],
            'rates.*.amount' => ['required', 'numeric', 'min:0'],
            'rates.*.date' => ['required', 'date'],
        ];
    }
}
