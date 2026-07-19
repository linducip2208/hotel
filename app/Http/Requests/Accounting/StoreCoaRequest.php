<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:gl_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'integer', 'exists:gl_accounts,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
