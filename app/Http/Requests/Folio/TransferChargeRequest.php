<?php

declare(strict_types=1);

namespace App\Http\Requests\Folio;

use Illuminate\Foundation\Http\FormRequest;

final class TransferChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'charge_id' => ['required', 'integer', 'exists:folio_charges,id'],
            'target_folio_id' => ['required', 'integer', 'exists:folios,id', 'different:folio_id'],
        ];
    }
}
