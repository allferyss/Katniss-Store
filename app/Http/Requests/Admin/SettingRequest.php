<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'order_notice' => ['nullable', 'string', 'max:300'],
            'footer_note' => ['nullable', 'string', 'max:300'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('katniss.uploads.max_kb')],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
