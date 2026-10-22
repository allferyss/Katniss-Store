@extends('layouts.app')

@section('content')
<section class="container-page py-10 md:py-16">
    <div class="grid items-center gap-10 md:grid-cols-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-400">Top-up game online</p>
            <h1 class="mt-3 text-4xl leading-tight md:text-5xl">Pilih game, isi Player ID, selesaikan pesanan.</h1>
            <p class="mt-4 max-w-lg text-slate-400">{{ setting('tagline') }} Cek status pesanan kapan saja dari akun Anda atau dengan nomor pesanan.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('games.index') }}" class="btn-primary">Lihat Katalog Game</a>
                <a href="{{ route('pages.how-to') }}" class="btn-secondary">Cara Top-Up</a>
            </div>
        </div>
        <div class="card md:p-8">
            <h2 class="text-lg">Cek status pesanan</h2>
            <p class="mt-1 text-sm text-slate-400">Masukkan nomor pesanan Anda (contoh: KS-20261021-XXXXXXXXXX).</p>
            <form method="GET" action="{{ route('tracking') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                <label for="home-order-number" class="sr-only">Nomor pesanan</label>
                <input id="home-order-number" name="order_number" class="input" placeholder="Nomor pesanan" maxlength="32" required>
                <button type="submit" class="btn-primary shrink-0">Cek</button>
            </form>
        </div>
    </div>
</section>

<section class="container-page py-8" aria-labelledby="games-heading">
    <div class="mb-5 flex items-end justify-between gap-4">
        <h2 id="games-heading" class="text-2xl">Game tersedia</h2>
        @if ($games->isNotEmpty())<a href="{{ route('games.index') }}" class="text-sm text-brand-400 hover:text-brand-300">Semua game →</a>@endif
    </div>

    @forelse ($games as $game)
        @if ($loop->first)<div class="grid grid-cols-2 gap-4 md:grid-cols-4">@endif
        <x-game-card :game="$game" />
        @if ($loop->last)</div>@endif
    @empty
        <x-empty-state title="Katalog masih kosong" text="Belum ada game yang aktif. Admin dapat menambahkan game dari panel admin.">
            @auth
                @if (auth()->user()->isAdmin())<a href="{{ route('admin.games.create') }}" class="btn-primary btn-sm">Tambah game</a>@endif
            @endauth
        </x-empty-state>
    @endforelse
</section>

<section class="container-page py-10" aria-labelledby="steps-heading">
    <h2 id="steps-heading" class="mb-5 text-2xl">Cara top-up</h2>
    <ol class="grid gap-4 md:grid-cols-4">
        @foreach ([
            ['Pilih game', 'Cari game di katalog dan buka halamannya.'],
            ['Pilih nominal & isi ID', 'Tentukan nominal, lalu isi Player ID (dan Server ID bila diminta).'],
            ['Buat pesanan & bayar', 'Periksa ringkasan, pilih metode pembayaran, lalu selesaikan pembayaran.'],
            ['Pantau status', 'Lihat perkembangan pesanan di riwayat pesanan Anda.'],
        ] as $i => [$title, $text])
            <li class="card">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600/20 text-sm font-bold text-brand-300">{{ $i + 1 }}</span>
                <h3 class="mt-3 text-base">{{ $title }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ $text }}</p>
            </li>
        @endforeach
    </ol>
</section>
@endsection
