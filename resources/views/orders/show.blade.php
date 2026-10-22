@extends('layouts.app')
@section('title', 'Pesanan '.$order->order_number)

@section('content')
@php
    $item = $order->items->first();
    $payment = $order->payments->sortByDesc('id')->first();
@endphp
<section class="container-page max-w-4xl py-8">
    <a href="{{ route('orders.index') }}" class="text-sm text-slate-400 hover:text-white">← Riwayat pesanan</a>
    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl">Pesanan <span class="font-mono text-xl">{{ $order->order_number }}</span></h1>
            <p class="text-sm text-slate-400">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-badge :color="$order->status->color()">Pesanan: {{ $order->status->label() }}</x-badge>
            <x-badge :color="$order->payment_status->color()">Pembayaran: {{ $order->payment_status->label() }}</x-badge>
        </div>
    </div>

    @if (is_simulation())
        <p class="mt-4 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            <strong>SIMULASI / DEMO.</strong> Pesanan ini tidak memproses uang maupun item game yang nyata.
        </p>
    @endif

    @if ($order->failure_reason)
        <p class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">Catatan: {{ $order->failure_reason }}</p>
    @endif

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="card">
            <h2 class="text-lg">Detail pesanan</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Game</dt><dd class="text-right text-white">{{ $item?->game_name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Produk</dt><dd class="text-right text-white">{{ $item?->product_name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Player ID</dt><dd class="break-all text-right text-white">{{ $order->player_id }}</dd></div>
                @if ($order->server_id)<div class="flex justify-between gap-3"><dt class="text-slate-400">Server ID</dt><dd class="text-right text-white">{{ $order->server_id }}</dd></div>@endif
                <hr class="border-line">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Harga</dt><dd>{{ rupiah($order->subtotal) }}</dd></div>
                @if ($order->discount_amount > 0)<div class="flex justify-between gap-3 text-emerald-300"><dt>Diskon {{ $order->promo_code }}</dt><dd>- {{ rupiah($order->discount_amount) }}</dd></div>@endif
                <div class="flex justify-between gap-3 text-base font-semibold text-white"><dt>Total</dt><dd>{{ rupiah($order->total) }}</dd></div>
            </dl>
        </div>

        <div class="card">
            <h2 class="text-lg">Pembayaran</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Metode</dt><dd class="text-right text-white">{{ $order->paymentMethodLabel() }}</dd></div>
                @if ($payment)<div class="flex justify-between gap-3"><dt class="text-slate-400">Referensi</dt><dd class="break-all text-right font-mono text-xs text-white">{{ $payment->reference }}</dd></div>@endif
                @if ($order->paid_at)<div class="flex justify-between gap-3"><dt class="text-slate-400">Dibayar</dt><dd class="text-right">{{ $order->paid_at->translatedFormat('d M Y H:i') }}</dd></div>@endif
                @if ($order->completed_at)<div class="flex justify-between gap-3"><dt class="text-slate-400">Selesai</dt><dd class="text-right">{{ $order->completed_at->translatedFormat('d M Y H:i') }}</dd></div>@endif
                @if ($order->topup_reference)<div class="flex justify-between gap-3"><dt class="text-slate-400">Ref. top-up</dt><dd class="break-all text-right font-mono text-xs">{{ $order->topup_reference }}</dd></div>@endif
            </dl>

            @if ($order->payment_status === \App\Enums\PaymentStatus::Pending && $order->status === \App\Enums\OrderStatus::Pending)
                @if (is_simulation())
                    <div class="mt-5 rounded-lg border border-line bg-ink p-4">
                        <p class="text-sm text-slate-300">Gunakan tombol berikut untuk mensimulasikan hasil pembayaran:</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('orders.simulate', $order) }}" data-once>@csrf<input type="hidden" name="result" value="success"><button class="btn-primary btn-sm" type="submit">Simulasikan bayar berhasil</button></form>
                            <form method="POST" action="{{ route('orders.simulate', $order) }}" data-confirm="Pesanan akan dibatalkan. Lanjutkan?">@csrf<input type="hidden" name="result" value="failed"><button class="btn-secondary btn-sm" type="submit">Simulasikan gagal</button></form>
                        </div>
                    </div>
                @else
                    <p class="mt-5 text-sm text-slate-300">Menunggu konfirmasi pembayaran dari penyedia pembayaran. Status akan diperbarui otomatis.</p>
                @endif
            @elseif ($order->status === \App\Enums\OrderStatus::Processing)
                <p class="mt-5 text-sm text-slate-300">Pembayaran diterima. Pesanan sedang diproses.</p>
            @endif
        </div>
    </div>
</section>
@endsection
