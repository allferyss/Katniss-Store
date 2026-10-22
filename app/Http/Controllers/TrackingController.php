<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/** Cek status pesanan publik berdasarkan nomor order (acak). Hanya menampilkan info non-sensitif. */
class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $number = strtoupper(trim((string) $request->query('order_number', '')));
        $order = null;

        if ($number !== '') {
            $request->validate(['order_number' => ['string', 'max:32']]);
            $order = Order::with('items')->where('order_number', $number)->first();
        }

        return view('orders.track', ['number' => $number, 'order' => $order, 'searched' => $number !== '']);
    }
}
