@extends('layouts.app')
@section('title', 'Katalog Game')

@section('content')
<section class="container-page py-8">
    <h1 class="text-3xl">Katalog Game</h1>
    <form method="GET" action="{{ route('games.index') }}" class="mt-5 flex max-w-xl gap-2" role="search">
        <label for="q" class="sr-only">Cari game</label>
        <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama game atau publisher…" maxlength="60">
        <button type="submit" class="btn-primary shrink-0">Cari</button>
        @if ($search !== '')<a href="{{ route('games.index') }}" class="btn-secondary shrink-0">Reset</a>@endif
    </form>

    <div class="mt-6">
        @forelse ($games as $game)
            @if ($loop->first)<div class="grid grid-cols-2 gap-4 md:grid-cols-4">@endif
            <x-game-card :game="$game" />
            @if ($loop->last)</div>@endif
        @empty
            <x-empty-state :title="$search !== '' ? 'Game tidak ditemukan' : 'Katalog masih kosong'"
                           :text="$search !== '' ? 'Coba kata kunci lain.' : 'Belum ada game yang aktif.'" />
        @endforelse
        {{ $games->links() }}
    </div>
</section>
@endsection
