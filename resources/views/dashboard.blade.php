@extends('layouts.app')
@section('title', 'Akun Saya')

@section('content')
<section class="container-page py-8">
    <h1 class="text-3xl">Halo, {{ auth()->user()->name }}</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="card"><p class="text-sm text-slate-400">Total pesanan</p><p class="mt-1 text-3xl font-bold text-white">{{ $stats['total'] }}</p></div>
        <div class="card"><p class="text-sm text-slate-400">Sedang berjalan</p><p class="mt-1 text-3xl font-bold text-white">{{ $stats['active'] }}</p></div>
        <div class="card"><p class="text-sm text-slate-400">Selesai</p><p class="mt-1 text-3xl font-bold text-white">{{ $stats['completed'] }}</p></div>
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl">Pesanan terbaru</h2>
        <div class="flex gap-2">
            <a href="{{ route('games.index') }}" class="btn-primary btn-sm">Top-up baru</a>
            <a href="{{ route('profile.edit') }}" class="btn-secondary btn-sm">Profil</a>
        </div>
    </div>

    <div class="mt-4">
        @if ($recent->isEmpty())
            <x-empty-state title="Belum ada pesanan" text="Mulai top-up pertama Anda dari katalog game." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th scope="col">No. Order</th><th scope="col">Produk</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                    @foreach ($recent as $order)
                        @php $item = $order->items->first(); @endphp
                        <tr>
                            <td class="font-mono text-xs">{{ $order->order_number }}</td>
                            <td>{{ $item?->game_name }} · {{ $item?->product_name }}</td>
                            <td>{{ rupiah($order->total) }}</td>
                            <td><x-badge :color="$order->status->color()">{{ $order->status->label() }}</x-badge></td>
                            <td class="text-right"><a href="{{ route('orders.show', $order) }}" class="btn-secondary btn-sm">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm"><a href="{{ route('orders.index') }}" class="text-brand-400 hover:text-brand-300">Lihat semua riwayat →</a></p>
        @endif
    </div>
</section>
@endsection
