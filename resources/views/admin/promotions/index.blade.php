@extends('layouts.admin')
@section('title', 'Promo')
@section('heading', 'Promo')
@section('actions')<a href="{{ route('admin.promotions.create') }}" class="btn-primary btn-sm">+ Buat promo</a>@endsection

@section('content')
@if ($promotions->isEmpty())
    <x-empty-state title="Belum ada promo" text="Buat kode promo sederhana (persen atau potongan tetap)." />
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">Kode</th><th scope="col">Nama</th><th scope="col">Potongan</th><th scope="col">Pemakaian</th><th scope="col">Periode</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
    <tbody>
    @foreach ($promotions as $p)
        <tr>
            <td class="font-mono text-xs text-white">{{ $p->code }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->type === 'percent' ? $p->value.'%' : rupiah($p->value) }}@if($p->max_discount) <span class="text-xs text-slate-400">(maks {{ rupiah($p->max_discount) }})</span>@endif</td>
            <td>{{ $p->used_count }}{{ $p->usage_limit ? ' / '.$p->usage_limit : '' }}</td>
            <td class="text-xs">{{ $p->starts_at?->format('d/m/Y') ?? '—' }} → {{ $p->ends_at?->format('d/m/Y') ?? '—' }}</td>
            <td><x-badge :color="$p->is_active ? 'green' : 'gray'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
            <td class="whitespace-nowrap text-right">
                <a class="btn-secondary btn-sm" href="{{ route('admin.promotions.edit', $p) }}">Edit</a>
                <form class="inline" method="POST" action="{{ route('admin.promotions.destroy', $p) }}" data-confirm="Hapus promo ini?">@csrf @method('DELETE')<button class="btn-danger btn-sm" type="submit">Hapus</button></form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $promotions->links() }}
@endif
@endsection
