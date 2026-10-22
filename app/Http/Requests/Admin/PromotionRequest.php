<?php

namespace App\Http\Requests\Admin;

use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => PromotionService::normalize((string) $this->input('code')),
            'is_active' => $this->boolean('is_active'),
            'min_purchase' => $this->filled('min_purchase') ? $this->input('min_purchase') : 0,
            'max_discount' => $this->filled('max_discount') ? $this->input('max_discount') : null,
            'usage_limit' => $this->filled('usage_limit') ? $this->input('usage_limit') : null,
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'ends_at' => $this->filled('ends_at') ? $this->input('ends_at') : null,
        ]);
    }

    public function rules(): array
    {
        $promotion = $this->route('promotion');
        $valueMax = $this->input('type') === Promotion::TYPE_PERCENT ? 90 : 10000000;

        return [
            'code' => ['required', 'string', 'max:32', 'regex:/^[A-Z0-9_\-]+$/', Rule::unique('promotions', 'code')->ignore($promotion?->id)],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in([Promotion::TYPE_PERCENT, Promotion::TYPE_FIXED])],
            'value' => ['required', 'integer', 'min:1', 'max:'.$valueMax],
            'min_purchase' => ['required', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['code.regex' => 'Kode hanya boleh huruf besar, angka, strip, dan garis bawah.'];
    }
}
