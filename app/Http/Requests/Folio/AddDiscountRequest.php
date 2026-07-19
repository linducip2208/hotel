<?php

declare(strict_types=1);

namespace App\Http\Requests\Folio;

use Illuminate\Foundation\Http\FormRequest;

final class AddDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:500'],
            'account_code' => ['nullable', 'string', 'exists:gl_accounts,code'],
        ];
    }
}
