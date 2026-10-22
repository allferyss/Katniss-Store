<?php

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code', 'name', 'type', 'value', 'min_purchase', 'max_discount',
        'starts_at', 'ends_at', 'usage_limit', 'used_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_purchase' => 'integer',
            'max_discount' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** Alasan promo tidak bisa dipakai, atau null bila valid. */
    public function unusableReason(int $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'Kode promo tidak aktif.';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Kode promo belum berlaku.';
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Kode promo sudah berakhir.';
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'Kuota kode promo sudah habis.';
        }
        if ($subtotal < $this->min_purchase) {
            return 'Minimal pembelian untuk kode promo ini '.rupiah($this->min_purchase).'.';
        }

        return null;
    }

    /** Hitung potongan harga (Rupiah, bilangan bulat). Tidak membuat total di bawah batas minimum. */
    public function discountFor(int $subtotal): int
    {
        $discount = $this->type === self::TYPE_PERCENT
            ? intdiv($subtotal * $this->value, 100)
            : $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, $this->max_discount);
        }

        $ceiling = max($subtotal - (int) config('katniss.min_order_total'), 0);

        return max(min($discount, $ceiling), 0);
    }
}
