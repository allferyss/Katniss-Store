<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public static function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Pratinjau diskon tanpa mengurangi kuota (dipakai di halaman checkout).
     *
     * @return array{0: Promotion, 1: int} [promo, potongan]
     */
    public function preview(string $code, int $subtotal): array
    {
        $promotion = Promotion::where('code', self::normalize($code))->first();

        return $this->evaluate($promotion, $subtotal);
    }

    /**
     * Memakai promo saat order dibuat. Harus dipanggil di dalam DB transaction:
     * baris dikunci agar kuota tidak bisa terlampaui oleh request paralel.
     *
     * @return array{0: Promotion, 1: int}
     */
    public function redeem(string $code, int $subtotal): array
    {
        $promotion = Promotion::where('code', self::normalize($code))->lockForUpdate()->first();

        [$promotion, $discount] = $this->evaluate($promotion, $subtotal);

        $promotion->increment('used_count');

        return [$promotion, $discount];
    }

    /** @return array{0: Promotion, 1: int} */
    private function evaluate(?Promotion $promotion, int $subtotal): array
    {
        if (! $promotion) {
            throw ValidationException::withMessages(['promo_code' => 'Kode promo tidak ditemukan.']);
        }

        if ($reason = $promotion->unusableReason($subtotal)) {
            throw ValidationException::withMessages(['promo_code' => $reason]);
        }

        return [$promotion, $promotion->discountFor($subtotal)];
    }
}
