<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    // Hanya diisi oleh OrderService (server-side), bukan langsung dari request pengguna.
    protected $fillable = [
        'order_number', 'user_id', 'player_id', 'server_id',
        'subtotal', 'discount_amount', 'total', 'promotion_id', 'promo_code',
        'status', 'payment_status', 'payment_method', 'idempotency_key',
        'topup_reference', 'failure_reason', 'paid_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total' => 'integer',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** Route binding memakai nomor order (acak) agar ID berurutan tidak terekspos. */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /** Nomor order unik & sulit ditebak, contoh: KS-20261021-7F3K9QXA2M */
    public static function generateNumber(): string
    {
        do {
            $number = 'KS-'.now()->format('Ymd').'-'.Str::upper(Str::random(10));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function paymentMethodLabel(): string
    {
        return config("katniss.payment.methods.{$this->payment_method}.label", $this->payment_method);
    }
}
