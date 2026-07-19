<?php

declare(strict_types=1);

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ppn_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'pb1_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'service_charge_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
