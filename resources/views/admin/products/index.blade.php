@extends('layouts.admin')
@section('title', 'Produk')
@section('heading', 'Produk / Nominal')
@section('actions')<a href="{{ route('admin.products.create', ['game_id' => $gameId]) }}" class="btn-primary btn-sm">+ Tambah produk</a>@endsection

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2" role="search">
    <div><label for="game_id" class="sr-only">Filter game</label>
        <select id="game_id" name="game_id" class="input"><option value="">Semua game</option>@foreach ($games as $g)<option value="{{ $g->id }}" @selected($gameId === $g->id)>{{ $g->name }}</option>@endforeach</select></div>
    <div><label for="q" class="sr-only">Cari produk</label><input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama produk…"></div>
    <button class="btn-secondary" type="submit">Filter</button>
</form>
@if ($products->isEmpty())
    <x-empty-state title="Belum ada produk" text="Tambahkan nominal top-up untuk sebuah game." />
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">Produk</th><th scope="col">Game</th><th scope="col">Harga jual</th><th scope="col">Harga modal</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
    <tbody>
    @foreach ($products as $p)
        <tr>
            <td class="font-medium text-white">{{ $p->name }}</td>
            <td>{{ $p->game?->name }}</td>
            <td>{{ rupiah($p->price) }}</td>
            <td>{{ $p->cost_price !== null ? rupiah($p->cost_price) : '—' }}</td>
            <td><x-badge :color="$p->is_active ? 'green' : 'gray'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
            <td class="whitespace-nowrap text-right">
                <a class="btn-secondary btn-sm" href="{{ route('admin.products.edit', $p) }}">Edit</a>
                <form class="inline" method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="Hapus produk ini?">@csrf @method('DELETE')<button class="btn-danger btn-sm" type="submit">Hapus</button></form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $products->links() }}
@endif
@endsection
