<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

final class SettleOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:cash,credit_card,debit_card,qris,charge_to_room'],
            'room_id' => ['required_if:payment_method,charge_to_room', 'integer', 'exists:rooms,id'],
            'amount_tendered' => ['required_if:payment_method,cash', 'numeric', 'min:0'],
        ];
    }
}
