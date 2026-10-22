<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Contracts\TopUpProvider;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\ProcessTopUpJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh logika pesanan, pembayaran, dan top-up berada di sini (bukan di controller).
 * Harga SELALU dihitung di server dari data produk, tidak pernah dari input client.
 */
class OrderService
{
    public function __construct(
        private PaymentGateway $gateway,
        private TopUpProvider $topUp,
        private PromotionService $promotions,
        private ActivityLogger $logger,
    ) {}

    /*
    |----------------------------------------------------------------------
    | Membuat pesanan
    |----------------------------------------------------------------------
    */

    /**
     * @param  array{product_id: int|string, player_id: string, server_id?: ?string, payment_method: string, promo_code?: ?string, idempotency_key?: ?string}  $data
     */
    public function createOrder(User $user, array $data): Order
    {
        $key = $data['idempotency_key'] ?? null;

        if ($key && $existing = $this->findByKey($user, $key)) {
            return $existing;
        }

        try {
            return DB::transaction(fn () => $this->buildOrder($user, $data));
        } catch (UniqueConstraintViolationException $e) {
            // Dua request dengan kunci sama berlomba: kembalikan pesanan yang sudah terbentuk.
            if ($key && $existing = $this->findByKey($user, $key)) {
                return $existing;
            }

            throw $e;
        }
    }

    private function findByKey(User $user, string $key): ?Order
    {
        return Order::where('idempotency_key', $key)->where('user_id', $user->id)->first();
    }

    private function buildOrder(User $user, array $data): Order
    {
        $product = Product::with('game')->lockForUpdate()->find($data['product_id']);
        $game = $product?->game;

        if (! $product || ! $product->is_active || ! $game || ! $game->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk atau game ini sedang tidak tersedia.',
            ]);
        }

        $serverId = $game->requires_server_id ? ($data['server_id'] ?? null) : null;
        if ($game->requires_server_id && blank($serverId)) {
            throw ValidationException::withMessages(['server_id' => 'Server ID wajib diisi untuk game ini.']);
        }

        $method = $data['payment_method'];
        if (! array_key_exists($method, config('katniss.payment.methods'))) {
            throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran tidak tersedia.']);
        }

        // Harga dihitung dari database, bukan dari request.
        $subtotal = (int) $product->price;
        $promotion = null;
        $discount = 0;

        if (filled($data['promo_code'] ?? null)) {
            [$promotion, $discount] = $this->promotions->redeem($data['promo_code'], $subtotal);
        }

        $order = Order::create([
            'order_number' => Order::generateNumber(),
            'user_id' => $user->id,
            'player_id' => $data['player_id'],
            'server_id' => $serverId,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total' => $subtotal - $discount,
            'promotion_id' => $promotion?->id,
            'promo_code' => $promotion?->code,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'payment_method' => $method,
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        $order->items()->create([
            'game_id' => $game->id,
            'product_id' => $product->id,
            'game_name' => $game->name,
            'product_name' => $product->name,
            'unit_price' => $subtotal,
            'quantity' => 1,
            'subtotal' => $subtotal,
        ]);

        $intent = $this->gateway->createPayment($order, $method);

        $order->payments()->create([
            'reference' => $intent->reference,
            'driver' => $intent->driver,
            'method' => $method,
            'amount' => $order->total,
            'status' => PaymentStatus::Pending,
            'payload' => $intent->instructions ? ['instructions' => $intent->instructions] : null,
        ]);

        return $order->load('items', 'payments');
    }

    /*
    |----------------------------------------------------------------------
    | Pembayaran
    |----------------------------------------------------------------------
    */

    /**
     * Menandai pembayaran lunas. Idempoten: memanggil dua kali tidak memproses ulang.
     *
     * @param  array<string, mixed>  $payload
     */
    public function markPaymentPaid(Payment $payment, array $payload = []): Order
    {
        $processed = false;

        $order = DB::transaction(function () use ($payment, $payload, &$processed) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Paid) {
                return $order; // sudah diproses sebelumnya
            }

            if ($payment->status !== PaymentStatus::Pending || $order->status !== OrderStatus::Pending) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran tidak dapat dikonfirmasi pada status saat ini.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'payload' => array_merge($payment->payload ?? [], $payload),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Processing,
                'paid_at' => now(),
            ]);

            $processed = true;

            return $order;
        });

        if ($processed && config('katniss.topup.auto_process')) {
            ProcessTopUpJob::dispatch($order->id);
        }

        return $order->refresh();
    }

    /** Pembayaran gagal/ditolak: pesanan dibatalkan dan kuota promo dikembalikan. */
    public function failPayment(Payment $payment, array $payload = []): Order
    {
        return DB::transaction(function () use ($payment, $payload) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Failed) {
                return $order;
            }

            if ($payment->status !== PaymentStatus::Pending || $order->status !== OrderStatus::Pending) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran tidak dapat digagalkan pada status saat ini.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => array_merge($payment->payload ?? [], $payload),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Failed,
                'status' => OrderStatus::Cancelled,
                'failure_reason' => 'Pembayaran gagal.',
            ]);

            $this->releasePromotion($order);

            return $order;
        });
    }

    /*
    |----------------------------------------------------------------------
    | Top-up
    |----------------------------------------------------------------------
    */

    /**
     * Memproses top-up ke supplier. Aman dipanggil berulang: hanya order berstatus
     * "processing" + "paid" yang belum punya topup_reference yang diproses.
     */
    public function processTopUp(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->find($orderId);

            if (! $order
                || $order->status !== OrderStatus::Processing
                || $order->payment_status !== PaymentStatus::Paid
                || $order->topup_reference) {
                return;
            }

            $result = $this->topUp->topUp($order);

            if ($result->success) {
                $order->update([
                    'status' => OrderStatus::Completed,
                    'completed_at' => now(),
                    'topup_reference' => $result->reference,
                    'failure_reason' => null,
                ]);
            } else {
                $order->update([
                    'status' => OrderStatus::Failed,
                    'failure_reason' => $result->message ?: 'Top-up gagal.',
                ]);
            }
        });
    }

    /*
    |----------------------------------------------------------------------
    | Pembatalan & perubahan status oleh admin
    |----------------------------------------------------------------------
    */

    public function cancelOrder(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->status->canTransitionTo(OrderStatus::Cancelled)) {
                throw ValidationException::withMessages(['order_status' => 'Pesanan tidak dapat dibatalkan pada status saat ini.']);
            }
            if ($order->payment_status === PaymentStatus::Paid) {
                throw ValidationException::withMessages(['order_status' => 'Pesanan yang sudah dibayar harus direfund terlebih dahulu.']);
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => in_array($order->payment_status, [PaymentStatus::Unpaid, PaymentStatus::Pending], true)
                    ? PaymentStatus::Failed
                    : $order->payment_status,
                'failure_reason' => $reason,
            ]);

            $order->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Failed->value]);
            $this->releasePromotion($order);

            return $order;
        });
    }

    /**
     * Ubah status pesanan dan/atau pembayaran dengan validasi transisi.
     * Dipakai oleh admin; customer tidak punya jalur ke method ini.
     */
    public function updateStatuses(Order $order, ?OrderStatus $newStatus, ?PaymentStatus $newPayment, User $actor): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $newPayment, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $before = ['status' => $order->status->value, 'payment_status' => $order->payment_status->value];
            $status = $order->status;
            $payment = $order->payment_status;
            $paymentChanged = false;

            if ($newPayment && $newPayment !== $payment) {
                if (! $payment->canTransitionTo($newPayment)) {
                    throw ValidationException::withMessages([
                        'payment_status' => "Status pembayaran tidak boleh diubah dari {$payment->label()} ke {$newPayment->label()}.",
                    ]);
                }
                $payment = $newPayment;
                $paymentChanged = true;
            }

            if ($newStatus && $newStatus !== $status) {
                if (! $status->canTransitionTo($newStatus)) {
                    throw ValidationException::withMessages([
                        'order_status' => "Status pesanan tidak boleh diubah dari {$status->label()} ke {$newStatus->label()}.",
                    ]);
                }
                $status = $newStatus;
            } elseif ($paymentChanged && $payment === PaymentStatus::Paid && $status === OrderStatus::Pending) {
                $status = OrderStatus::Processing; // lunas -> otomatis diproses
            }

            if (in_array($status, [OrderStatus::Processing, OrderStatus::Completed], true) && $payment !== PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order_status' => 'Pesanan hanya bisa diproses/diselesaikan setelah pembayaran berstatus Dibayar.',
                ]);
            }
            if ($status === OrderStatus::Cancelled && $payment === PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order_status' => 'Pesanan yang sudah dibayar tidak bisa dibatalkan sebelum pembayaran di-refund.',
                ]);
            }

            $becameCancelled = $status === OrderStatus::Cancelled && $order->status !== OrderStatus::Cancelled;

            $order->fill([
                'status' => $status,
                'payment_status' => $payment,
                'paid_at' => ($paymentChanged && $payment === PaymentStatus::Paid) ? now() : $order->paid_at,
                'completed_at' => ($status === OrderStatus::Completed && $order->status !== OrderStatus::Completed) ? now() : $order->completed_at,
            ])->save();

            if ($paymentChanged) {
                $order->payments()->latest('id')->first()?->update([
                    'status' => $payment,
                    'paid_at' => $payment === PaymentStatus::Paid ? now() : null,
                ]);
            }

            if ($becameCancelled) {
                $this->releasePromotion($order);
            }

            $this->logger->log(
                'order.status_updated',
                "Mengubah status pesanan {$order->order_number}",
                $order,
                ['before' => $before, 'after' => ['status' => $status->value, 'payment_status' => $payment->value]],
            );

            return $order;
        });
    }

    /** Kembalikan kuota promo bila pesanan batal/gagal bayar. */
    private function releasePromotion(Order $order): void
    {
        if ($order->promotion_id) {
            Promotion::whereKey($order->promotion_id)->where('used_count', '>', 0)->decrement('used_count');
        }
    }
}
