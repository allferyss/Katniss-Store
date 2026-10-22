@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
@php $max = max($chart->max('total'), 1); @endphp
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['Total pesanan', $stats['orders']], ['Menunggu', $stats['pending']], ['Diproses', $stats['processing']], ['Selesai', $stats['completed']],
        ['Gagal', $stats['failed']], ['Dibatalkan', $stats['cancelled']], ['Game', $stats['games']], ['Customer', $stats['customers']],
    ] as [$label, $value])
        <div class="card"><p class="text-sm text-slate-400">{{ $label }}</p><p class="mt-1 text-3xl font-bold text-white">{{ number_format($value, 0, ',', '.') }}</p></div>
    @endforeach
</div>

<div class="card mt-4">
    <p class="text-sm text-slate-400">Total penjualan (pembayaran berstatus Dibayar)</p>
    <p class="mt-1 text-3xl font-bold text-white">{{ rupiah($stats['sales']) }}</p>
</div>

<div class="card mt-4">
    <h2 class="text-lg">Penjualan 7 hari terakhir</h2>
    <div class="mt-4 flex h-40 items-end gap-2" role="img" aria-label="Grafik batang penjualan 7 hari terakhir">
        @foreach ($chart as $day)
            <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                <div class="w-full rounded-t bg-brand-600" style="height: {{ max(round($day['total'] / $max * 100), $day['total'] > 0 ? 3 : 0) }}%" title="{{ rupiah($day['total']) }}"></div>
                <span class="text-[10px] text-slate-400">{{ $day['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-6 flex items-center justify-between"><h2 class="text-lg">Pesanan terbaru</h2><a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-400 hover:text-brand-300">Semua pesanan →</a></div>
<div class="mt-3">
    @if ($recent->isEmpty())
        <x-empty-state title="Belum ada pesanan" />
    @else
        <div class="table-wrap"><table class="table">
            <thead><tr><th scope="col">No. Order</th><th scope="col">Customer</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
            @foreach ($recent as $o)
                <tr>
                    <td class="font-mono text-xs">{{ $o->order_number }}</td>
                    <td>{{ $o->user?->name }}</td>
                    <td>{{ rupiah($o->total) }}</td>
                    <td><x-badge :color="$o->status->color()">{{ $o->status->label() }}</x-badge></td>
                    <td class="text-right"><a class="btn-secondary btn-sm" href="{{ route('admin.orders.show', $o) }}">Detail</a></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    @endif
</div>
@endsection
