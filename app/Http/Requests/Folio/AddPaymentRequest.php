<?php

declare(strict_types=1);

namespace App\Http\Requests\Folio;

use Illuminate\Foundation\Http\FormRequest;

final class AddPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,credit_card,debit_card,bank_transfer,qris,voucher,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
