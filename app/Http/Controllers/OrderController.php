<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Riwayat pesanan milik customer yang sedang login. */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load('items', 'payments');

        return view('orders.show', compact('order'));
    }
}
