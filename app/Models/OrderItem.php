<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // Snapshot nama & harga saat transaksi: tidak berubah walau produk diedit/dihapus.
    protected $fillable = [
        'order_id', 'game_id', 'product_id', 'game_name', 'product_name',
        'unit_price', 'quantity', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
