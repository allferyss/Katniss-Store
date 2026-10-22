<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * Halaman pembayaran SIMULASI untuk demo. Hanya aktif bila PAYMENT_DRIVER=fake.
 * Dengan gateway nyata, route ini mengembalikan 404 dan status hanya berubah lewat webhook terverifikasi.
 */
class PaymentSimulationController extends Controller
{
    public function __invoke(Request $request, Order $order)
    {
        // Cek mode simulasi SEBELUM menyelesaikan dependency gateway apa pun.
        abort_unless(is_simulation(), 404);
        $this->authorize('view', $order);

        $data = $request->validate(['result' => ['required', 'in:success,failed']]);

        $orders = app(OrderService::class);

        $payment = $order->payments()->where('status', PaymentStatus::Pending->value)->latest('id')->first()
            ?? $order->payments()->latest('id')->firstOrFail();

        if ($data['result'] === 'success') {
            $orders->markPaymentPaid($payment, ['simulated' => true]);
            $message = 'SIMULASI: pembayaran ditandai berhasil.';
        } else {
            $orders->failPayment($payment, ['simulated' => true]);
            $message = 'SIMULASI: pembayaran ditandai gagal dan pesanan dibatalkan.';
        }

        return redirect()->route('orders.show', $order)->with('success', $message);
    }
}
