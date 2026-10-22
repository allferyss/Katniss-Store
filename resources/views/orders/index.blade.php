@extends('layouts.app')
@section('title', 'Riwayat Pesanan')

@section('content')
<section class="container-page py-8">
    <h1 class="text-3xl">Riwayat Pesanan</h1>

    <div class="mt-6">
        @if ($orders->isEmpty())
            <x-empty-state title="Belum ada pesanan" text="Pesanan yang Anda buat akan muncul di sini.">
                <a href="{{ route('games.index') }}" class="btn-primary btn-sm">Mulai top-up</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th scope="col">No. Order</th><th scope="col">Produk</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Pembayaran</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                    @foreach ($orders as $order)
                        @php $item = $order->items->first(); @endphp
                        <tr>
                            <td class="font-mono text-xs">{{ $order->order_number }}<br><span class="text-slate-500">{{ $order->created_at->translatedFormat('d M Y H:i') }}</span></td>
                            <td>{{ $item?->game_name }}<br><span class="text-xs text-slate-400">{{ $item?->product_name }}</span></td>
                            <td>{{ rupiah($order->total) }}</td>
                            <td><x-badge :color="$order->status->color()">{{ $order->status->label() }}</x-badge></td>
                            <td><x-badge :color="$order->payment_status->color()">{{ $order->payment_status->label() }}</x-badge></td>
                            <td class="text-right"><a href="{{ route('orders.show', $order) }}" class="btn-secondary btn-sm">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        @endif
    </div>
</section>
@endsection
