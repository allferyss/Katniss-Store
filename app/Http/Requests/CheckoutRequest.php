<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\PromotionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Dipakai untuk halaman ringkasan checkout (GET) dan pembuatan pesanan (POST).
 * Field harga/total/status TIDAK diterima dari client; harga dihitung di server.
 */
class CheckoutRequest extends FormRequest
{
    private ?Product $resolvedProduct = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('promo_code')) {
            $this->merge(['promo_code' => PromotionService::normalize((string) $this->input('promo_code'))]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'player_id' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_\-#.]+$/'],
            'server_id' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9_\-#.]+$/'],
            'promo_code' => ['nullable', 'string', 'max:32', 'regex:/^[A-Z0-9_\-]+$/'],
        ];

        if ($this->isMethod('POST')) {
            $rules['payment_method'] = ['required', Rule::in(array_keys(config('katniss.payment.methods')))];
            $rules['idempotency_key'] = ['required', 'uuid'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'player_id.regex' => 'Player ID hanya boleh berisi huruf, angka, dan karakter _ - # .',
            'server_id.regex' => 'Server ID hanya boleh berisi huruf, angka, dan karakter _ - # .',
            'promo_code.regex' => 'Format kode promo tidak valid.',
            'product_id.exists' => 'Produk tidak ditemukan atau sedang tidak tersedia.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('product_id')) {
                return;
            }

            $game = $this->product()?->game;

            if (! $game || ! $game->is_active) {
                $validator->errors()->add('product_id', 'Game ini sedang tidak tersedia.');

                return;
            }

            if ($game->requires_server_id && blank($this->input('server_id'))) {
                $validator->errors()->add('server_id', 'Server ID wajib diisi untuk game ini.');
            }
        });
    }

    /** Produk yang dipilih (dimuat dari database sekali per request). */
    public function product(): ?Product
    {
        return $this->resolvedProduct ??= Product::with('game')->find($this->input('product_id'));
    }
}
