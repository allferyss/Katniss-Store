<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /** Ringkasan pesanan sebelum dibuat. Semua harga dihitung di server. */
    public function show(CheckoutRequest $request, PromotionService $promotions)
    {
        $product = $request->product();
        $subtotal = (int) $product->price;
        $discount = 0;
        $promoError = null;
        $promotion = null;

        if ($request->filled('promo_code')) {
            try {
                [$promotion, $discount] = $promotions->preview($request->input('promo_code'), $subtotal);
            } catch (ValidationException $e) {
                $promoError = $e->errors()['promo_code'][0] ?? 'Kode promo tidak valid.';
            }
        }

        return view('checkout.show', [
            'product' => $product,
            'game' => $product->game,
            'playerId' => $request->input('player_id'),
            'serverId' => $product->game->requires_server_id ? $request->input('server_id') : null,
            'promoCode' => $promotion?->code,
            'promoError' => $promoError,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'methods' => config('katniss.payment.methods'),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders)
    {
        $order = $orders->createOrder($request->user(), $request->safe()->only([
            'product_id', 'player_id', 'server_id', 'payment_method', 'promo_code', 'idempotency_key',
        ]));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
    }
}
