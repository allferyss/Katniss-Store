@extends('layouts.admin')
@section('title', 'Game')
@section('heading', 'Game')
@section('actions')<a href="{{ route('admin.games.create') }}" class="btn-primary btn-sm">+ Tambah game</a>@endsection

@section('content')
<form method="GET" class="mb-4 flex max-w-md gap-2" role="search">
    <label for="q" class="sr-only">Cari game</label>
    <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama game…">
    <button class="btn-secondary shrink-0" type="submit">Cari</button>
</form>
@if ($games->isEmpty())
    <x-empty-state title="Belum ada game" text="Tambahkan game pertama untuk mengisi katalog."><a href="{{ route('admin.games.create') }}" class="btn-primary btn-sm">Tambah game</a></x-empty-state>
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">Game</th><th scope="col">Slug</th><th scope="col">Produk</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
    <tbody>
    @foreach ($games as $g)
        <tr>
            <td><div class="flex items-center gap-3"><img src="{{ $g->image_url }}" alt="" class="h-10 w-10 rounded object-cover"><span class="font-medium text-white">{{ $g->name }}</span></div></td>
            <td class="font-mono text-xs">{{ $g->slug }}</td>
            <td><a class="text-brand-400 hover:text-brand-300" href="{{ route('admin.products.index', ['game_id' => $g->id]) }}">{{ $g->products_count }} produk</a></td>
            <td><x-badge :color="$g->is_active ? 'green' : 'gray'">{{ $g->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
            <td class="whitespace-nowrap text-right">
                <a class="btn-secondary btn-sm" href="{{ route('admin.games.edit', $g) }}">Edit</a>
                <form class="inline" method="POST" action="{{ route('admin.games.destroy', $g) }}" data-confirm="Hapus game ini beserta semua produknya?">@csrf @method('DELETE')<button class="btn-danger btn-sm" type="submit">Hapus</button></form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $games->links() }}
@endif
@endsection
