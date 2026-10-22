@extends('layouts.app')
@section('title', 'Cek Pesanan')

@section('content')
<section class="container-page max-w-2xl py-8">
    <h1 class="text-3xl">Cek Status Pesanan</h1>
    <form method="GET" action="{{ route('tracking') }}" class="card mt-6">
        <label for="order_number" class="label">Nomor pesanan</label>
        <div class="flex gap-2">
            <input id="order_number" name="order_number" value="{{ $number }}" class="input uppercase" placeholder="KS-20261021-XXXXXXXXXX" maxlength="32" required>
            <button type="submit" class="btn-primary shrink-0">Cek</button>
        </div>
        <p class="hint">Nomor pesanan ada di halaman pesanan dan riwayat akun Anda.</p>
    </form>

    @if ($searched)
        <div class="mt-6" aria-live="polite">
            @if ($order)
                @php $item = $order->items->first(); @endphp
                <div class="card">
                    <p class="font-mono text-sm text-slate-300">{{ $order->order_number }}</p>
                    <p class="mt-1 text-lg font-semibold text-white">{{ $item?->game_name }} · {{ $item?->product_name }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-badge :color="$order->status->color()">Pesanan: {{ $order->status->label() }}</x-badge>
                        <x-badge :color="$order->payment_status->color()">Pembayaran: {{ $order->payment_status->label() }}</x-badge>
                    </div>
                    <p class="mt-3 text-sm text-slate-400">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}. Untuk detail lengkap, masuk ke akun pemilik pesanan.</p>
                    @if (is_simulation())<p class="mt-2 text-xs text-amber-200">SIMULASI / DEMO: bukan transaksi nyata.</p>@endif
                </div>
            @else
                <x-empty-state title="Pesanan tidak ditemukan" text="Periksa kembali nomor pesanan Anda." />
            @endif
        </div>
    @endif
</section>
@endsection
