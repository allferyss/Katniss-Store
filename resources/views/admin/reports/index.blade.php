@extends('layouts.admin')
@section('title', 'Laporan')
@section('heading', 'Laporan Transaksi')
@section('actions')<a href="{{ route('admin.reports.export', array_filter($filters)) }}" class="btn-secondary btn-sm">Unduh CSV</a>@endsection

@section('content')
<form method="GET" class="card mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div><label for="from" class="label">Dari</label><input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="input"></div>
    <div><label for="to" class="label">Sampai</label><input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="input"></div>
    <div><label for="status" class="label">Status pesanan</label><select id="status" name="status" class="input"><option value="">Semua</option>@foreach (\App\Enums\OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label for="payment_status" class="label">Pembayaran</label><select id="payment_status" name="payment_status" class="input"><option value="">Semua</option>@foreach (\App\Enums\PaymentStatus::cases() as $s)<option value="{{ $s->value }}" @selected($filters['payment_status'] === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div class="flex items-end"><button class="btn-primary" type="submit">Tampilkan</button></div>
</form>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card"><p class="text-sm text-slate-400">Jumlah pesanan</p><p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['count'], 0, ',', '.') }}</p></div>
    <div class="card"><p class="text-sm text-slate-400">Nilai semua pesanan</p><p class="mt-1 text-2xl font-bold text-white">{{ rupiah($summary['gross']) }}</p></div>
    <div class="card"><p class="text-sm text-slate-400">Penjualan (Dibayar)</p><p class="mt-1 text-2xl font-bold text-white">{{ rupiah($summary['paid']) }}</p></div>
    <div class="card"><p class="text-sm text-slate-400">Total diskon (Dibayar)</p><p class="mt-1 text-2xl font-bold text-white">{{ rupiah($summary['discount']) }}</p></div>
</div>

<div class="mt-6">
@if ($orders->isEmpty())
    <x-empty-state title="Tidak ada transaksi" text="Tidak ada pesanan pada rentang tanggal dan filter ini." />
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">No. Order</th><th scope="col">Tanggal</th><th scope="col">Customer</th><th scope="col">Produk</th><th scope="col">Total</th><th scope="col">Pesanan</th><th scope="col">Pembayaran</th></tr></thead>
    <tbody>
    @foreach ($orders as $o)
        @php $item = $o->items->first(); @endphp
        <tr>
            <td class="font-mono text-xs"><a class="text-brand-400 hover:text-brand-300" href="{{ route('admin.orders.show', $o) }}">{{ $o->order_number }}</a></td>
            <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $o->user?->name }}</td>
            <td>{{ $item?->game_name }} · {{ $item?->product_name }}</td>
            <td>{{ rupiah($o->total) }}</td>
            <td><x-badge :color="$o->status->color()">{{ $o->status->label() }}</x-badge></td>
            <td><x-badge :color="$o->payment_status->color()">{{ $o->payment_status->label() }}</x-badge></td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $orders->links() }}
@endif
</div>
@endsection
