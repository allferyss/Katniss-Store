@extends('layouts.app')
@section('title', 'Top-Up '.$game->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $game->description), 150))

@section('content')
<section class="container-page py-8">
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate-400">
        <a href="{{ route('games.index') }}" class="hover:text-white">Katalog</a> <span aria-hidden="true">/</span> <span class="text-slate-200">{{ $game->name }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
        <aside>
            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="aspect-[4/3] w-full rounded-xl border border-line object-cover">
            <h1 class="mt-4 text-2xl">{{ $game->name }}</h1>
            @if ($game->publisher)<p class="text-sm text-slate-400">{{ $game->publisher }}</p>@endif
            @if ($game->description)<p class="mt-3 whitespace-pre-line text-sm text-slate-300">{{ $game->description }}</p>@endif
        </aside>

        <div>
            @if ($products->isEmpty())
                <x-empty-state title="Belum ada nominal tersedia" text="Produk untuk game ini belum tersedia. Silakan cek lagi nanti." />
            @else
                <form method="GET" action="{{ route('checkout.show') }}" class="space-y-8" id="order-form">
                    <fieldset>
                        <legend class="mb-3 text-lg font-semibold text-white">1. Pilih nominal</legend>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach ($products as $product)
                                <label class="relative block cursor-pointer">
                                    <input type="radio" name="product_id" value="{{ $product->id }}" class="peer sr-only" required @checked((int) old('product_id', request('product_id')) === $product->id)>
                                    <span class="block h-full rounded-xl border border-line bg-surface p-4 transition-colors hover:border-brand-500/60 peer-checked:border-brand-500 peer-checked:bg-brand-600/10 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
                                        <span class="block text-sm font-semibold text-white">{{ $product->name }}</span>
                                        @if ($product->description)<span class="mt-0.5 block text-xs text-slate-400">{{ $product->description }}</span>@endif
                                        <span class="mt-2 block font-semibold text-brand-300">{{ rupiah($product->price) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('product_id')<p class="field-error">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset>
                        <legend class="mb-3 text-lg font-semibold text-white">2. Masukkan data akun game</legend>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field name="player_id" label="Player ID" :value="request('player_id')" required maxlength="32" autocomplete="off" placeholder="Contoh: 123456789" />
                            @if ($game->requires_server_id)
                                <x-field name="server_id" label="Server ID" :value="request('server_id')" required maxlength="20" autocomplete="off" placeholder="Contoh: 1234" />
                            @endif
                        </div>
                        @if ($game->id_hint)
                            <details class="mt-4 rounded-lg border border-line bg-surface p-4 text-sm">
                                <summary class="cursor-pointer font-medium text-brand-300">Di mana menemukan Player ID{{ $game->requires_server_id ? ' / Server ID' : '' }}?</summary>
                                <p class="mt-2 whitespace-pre-line text-slate-300">{{ $game->id_hint }}</p>
                            </details>
                        @endif
                        <p class="hint">Jangan pernah membagikan password akun game Anda. Kami hanya membutuhkan ID.</p>
                    </fieldset>

                    <button type="submit" class="btn-primary w-full sm:w-auto">Lanjut ke Checkout</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
