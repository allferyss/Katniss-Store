<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
            'requires_server_id' => $this->boolean('requires_server_id'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $game = $this->route('game');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('games', 'slug')->ignore($game?->id)],
            'publisher' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:3000'],
            'id_hint' => ['nullable', 'string', 'max:1000'],
            'requires_server_id' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('katniss.uploads.max_kb')],
        ];
    }
}
