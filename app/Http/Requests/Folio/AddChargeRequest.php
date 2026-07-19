<?php

declare(strict_types=1);

namespace App\Http\Requests\Folio;

use Illuminate\Foundation\Http\FormRequest;

final class AddChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_code' => ['nullable', 'string', 'exists:gl_accounts,code'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'charge_date' => ['nullable', 'date'],
        ];
    }
}
