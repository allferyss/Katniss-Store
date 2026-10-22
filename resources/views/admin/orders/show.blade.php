@extends('layouts.admin')
@section('title', 'Pesanan '.$order->order_number)
@section('heading', 'Detail Pesanan')
@section('actions')<a href="{{ route('admin.orders.index') }}" class="btn-secondary btn-sm">← Daftar pesanan</a>@endsection

@section('content')
@php
    $item = $order->items->first();
    $statusOptions = array_merge([$order->status], $order->status->transitions());
    $paymentOptions = array_merge([$order->payment_status], $order->payment_status->transitions());
@endphp
<div class="grid gap-6 lg:grid-cols-2">
    <div class="card">
        <p class="font-mono text-sm text-white">{{ $order->order_number }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-badge :color="$order->status->color()">Pesanan: {{ $order->status->label() }}</x-badge>
            <x-badge :color="$order->payment_status->color()">Pembayaran: {{ $order->payment_status->label() }}</x-badge>
        </div>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Customer</dt><dd class="text-right">{{ $order->user?->name }} ({{ $order->user?->email }})</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Game / produk</dt><dd class="text-right">{{ $item?->game_name }} · {{ $item?->product_name }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Player ID</dt><dd class="break-all text-right">{{ $order->player_id }}</dd></div>
            @if ($order->server_id)<div class="flex justify-between gap-3"><dt class="text-slate-400">Server ID</dt><dd>{{ $order->server_id }}</dd></div>@endif
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Harga</dt><dd>{{ rupiah($order->subtotal) }}</dd></div>
            @if ($order->discount_amount > 0)<div class="flex justify-between gap-3"><dt class="text-slate-400">Diskon {{ $order->promo_code }}</dt><dd>- {{ rupiah($order->discount_amount) }}</dd></div>@endif
            <div class="flex justify-between gap-3 font-semibold text-white"><dt>Total</dt><dd>{{ rupiah($order->total) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Metode</dt><dd>{{ $order->paymentMethodLabel() }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Dibuat</dt><dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
            @if ($order->paid_at)<div class="flex justify-between gap-3"><dt class="text-slate-400">Dibayar</dt><dd>{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>@endif
            @if ($order->completed_at)<div class="flex justify-between gap-3"><dt class="text-slate-400">Selesai</dt><dd>{{ $order->completed_at->format('d/m/Y H:i') }}</dd></div>@endif
            @if ($order->topup_reference)<div class="flex justify-between gap-3"><dt class="text-slate-400">Ref. top-up</dt><dd class="font-mono text-xs">{{ $order->topup_reference }}</dd></div>@endif
            @if ($order->failure_reason)<div class="flex justify-between gap-3"><dt class="text-slate-400">Catatan</dt><dd class="text-right text-red-300">{{ $order->failure_reason }}</dd></div>@endif
        </dl>
    </div>

    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="card space-y-4" data-confirm="Simpan perubahan status?">
            @csrf @method('PATCH')
            <h2 class="text-lg">Ubah status</h2>
            <div>
                <label for="order_status" class="label">Status pesanan</label>
                <select id="order_status" name="order_status" class="input">@foreach ($statusOptions as $s)<option value="{{ $s->value }}" @selected($s === $order->status)>{{ $s->label() }}</option>@endforeach</select>
            </div>
            <div>
                <label for="payment_status" class="label">Status pembayaran</label>
                <select id="payment_status" name="payment_status" class="input">@foreach ($paymentOptions as $s)<option value="{{ $s->value }}" @selected($s === $order->payment_status)>{{ $s->label() }}</option>@endforeach</select>
            </div>
            <p class="hint">Hanya transisi yang valid yang ditampilkan. Pesanan hanya bisa Diproses/Selesai bila pembayaran Dibayar.</p>
            <button class="btn-primary" type="submit">Simpan status</button>
        </form>

        @if ($order->status === \App\Enums\OrderStatus::Processing && $order->payment_status === \App\Enums\PaymentStatus::Paid && ! $order->topup_reference)
            <form method="POST" action="{{ route('admin.orders.topup', $order) }}" class="card" data-once>
                @csrf
                <h2 class="text-lg">Proses top-up</h2>
                <p class="mt-1 text-sm text-slate-400">{{ is_simulation() ? 'SIMULASI: tidak ada top-up nyata yang dikirim ke game.' : 'Kirim top-up ke supplier.' }}</p>
                <button class="btn-primary mt-3" type="submit">Jalankan top-up</button>
            </form>
        @endif

        <div class="card">
            <h2 class="text-lg">Riwayat pembayaran</h2>
            @forelse ($order->payments as $pay)
                <div class="mt-3 flex items-center justify-between gap-3 border-t border-line pt-3 text-sm first:border-0 first:pt-0">
                    <div><p class="font-mono text-xs">{{ $pay->reference }}</p><p class="text-xs text-slate-400">{{ $pay->driver }} · {{ rupiah($pay->amount) }}</p></div>
                    <x-badge :color="$pay->status->color()">{{ $pay->status->label() }}</x-badge>
                </div>
            @empty
                <p class="mt-2 text-sm text-slate-400">Belum ada data pembayaran.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
