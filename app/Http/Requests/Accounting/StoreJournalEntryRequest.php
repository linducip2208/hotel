<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:gl_accounts,id'],
            'lines.*.debit' => ['required_without:lines.*.credit', 'numeric', 'min:0'],
            'lines.*.credit' => ['required_without:lines.*.debit', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
