<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'cost_price' => $this->filled('cost_price') ? $this->input('cost_price') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', Rule::exists('games', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'cost_price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'sku' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
        ];
    }
}
