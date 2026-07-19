<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:pos_menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
