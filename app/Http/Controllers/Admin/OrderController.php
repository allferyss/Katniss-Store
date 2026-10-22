<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Jobs\ProcessTopUpJob;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $orders = Order::with(['user', 'items'])
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($w) => $w
                    ->where('order_number', 'like', "%{$q}%")
                    ->orWhere('player_id', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")));
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['user', 'items', 'payments', 'promotion']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order, OrderService $orders)
    {
        $this->authorize('update', $order);

        $orders->updateStatuses(
            $order,
            OrderStatus::from($request->validated('order_status')),
            PaymentStatus::from($request->validated('payment_status')),
            $request->user(),
        );

        return redirect()->route('admin.orders.show', $order)->with('success', 'Status pesanan diperbarui.');
    }

    /** Menjalankan top-up (simulasi) untuk pesanan yang sudah dibayar. */
    public function topup(Order $order, ActivityLogger $logger)
    {
        $this->authorize('update', $order);

        if ($order->status !== OrderStatus::Processing || $order->payment_status !== PaymentStatus::Paid || $order->topup_reference) {
            return back()->with('error', 'Top-up hanya bisa dijalankan untuk pesanan berstatus Diproses dan sudah Dibayar.');
        }

        $logger->log('order.topup_started', "Menjalankan top-up pesanan {$order->order_number}", $order);
        ProcessTopUpJob::dispatch($order->id);

        return back()->with('success', is_simulation()
            ? 'Top-up SIMULASI dijalankan. Tidak ada top-up nyata yang dikirim ke game.'
            : 'Top-up dijalankan.');
    }
}
