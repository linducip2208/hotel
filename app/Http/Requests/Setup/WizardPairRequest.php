<?php

declare(strict_types=1);

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;

final class WizardPairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:100'],
            'activation_code' => ['required', 'string', 'max:100'],
            'domain' => ['required', 'string', 'max:255'],
        ];
    }
}
