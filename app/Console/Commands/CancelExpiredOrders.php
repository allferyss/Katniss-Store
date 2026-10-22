<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CancelExpiredOrders extends Command
{
    protected $signature = 'katniss:cancel-expired-orders';

    protected $description = 'Membatalkan pesanan yang belum dibayar melewati batas waktu (ORDER_EXPIRE_HOURS).';

    public function handle(OrderService $orders): int
    {
        $hours = (int) config('katniss.payment.expire_hours');
        $count = 0;

        Order::query()
            ->where('status', OrderStatus::Pending->value)
            ->whereIn('payment_status', [PaymentStatus::Unpaid->value, PaymentStatus::Pending->value])
            ->where('created_at', '<', now()->subHours($hours))
            ->chunkById(100, function ($chunk) use ($orders, &$count) {
                foreach ($chunk as $order) {
                    try {
                        $orders->cancelOrder($order, 'Dibatalkan otomatis: pembayaran melewati batas waktu.');
                        $count++;
                    } catch (ValidationException) {
                        // Status berubah di antara query dan proses: lewati.
                    }
                }
            });

        $this->info("{$count} pesanan kedaluwarsa dibatalkan.");

        return self::SUCCESS;
    }
}
