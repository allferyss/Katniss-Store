@extends('layouts.admin')
@section('title', 'Pesanan')
@section('heading', 'Pesanan')

@section('content')
<form method="GET" class="card mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6" role="search">
    <div class="lg:col-span-2"><label for="q" class="label">Cari</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="No. order, Player ID, nama/email"></div>
    <div><label for="status" class="label">Status pesanan</label>
        <select id="status" name="status" class="input"><option value="">Semua</option>@foreach (\App\Enums\OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected(($filters['status'] ?? null) === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label for="payment_status" class="label">Pembayaran</label>
        <select id="payment_status" name="payment_status" class="input"><option value="">Semua</option>@foreach (\App\Enums\PaymentStatus::cases() as $s)<option value="{{ $s->value }}" @selected(($filters['payment_status'] ?? null) === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label for="from" class="label">Dari</label><input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input"></div>
    <div><label for="to" class="label">Sampai</label><input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input"></div>
    <div class="flex gap-2 sm:col-span-2 lg:col-span-6"><button class="btn-primary btn-sm" type="submit">Terapkan</button><a href="{{ route('admin.orders.index') }}" class="btn-secondary btn-sm">Reset</a></div>
</form>

@if ($orders->isEmpty())
    <x-empty-state title="Tidak ada pesanan" text="Belum ada pesanan yang cocok dengan filter." />
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">No. Order</th><th scope="col">Customer</th><th scope="col">Produk</th><th scope="col">Total</th><th scope="col">Pesanan</th><th scope="col">Pembayaran</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
    <tbody>
    @foreach ($orders as $o)
        @php $item = $o->items->first(); @endphp
        <tr>
            <td class="font-mono text-xs">{{ $o->order_number }}<br><span class="text-slate-500">{{ $o->created_at->format('d/m/Y H:i') }}</span></td>
            <td>{{ $o->user?->name }}<br><span class="text-xs text-slate-400">{{ $o->user?->email }}</span></td>
            <td>{{ $item?->game_name }}<br><span class="text-xs text-slate-400">{{ $item?->product_name }} · {{ $o->player_id }}</span></td>
            <td>{{ rupiah($o->total) }}</td>
            <td><x-badge :color="$o->status->color()">{{ $o->status->label() }}</x-badge></td>
            <td><x-badge :color="$o->payment_status->color()">{{ $o->payment_status->label() }}</x-badge></td>
            <td class="text-right"><a class="btn-secondary btn-sm" href="{{ route('admin.orders.show', $o) }}">Detail</a></td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $orders->links() }}
@endif
@endsection
