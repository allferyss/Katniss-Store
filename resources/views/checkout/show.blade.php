@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<section class="container-page max-w-4xl py-8">
    <h1 class="text-3xl">Checkout</h1>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="space-y-6">
            {{-- Pratinjau promo (form terpisah, metode GET) --}}
            <form method="GET" action="{{ route('checkout.show') }}" class="card">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="player_id" value="{{ $playerId }}">
                @if ($serverId)<input type="hidden" name="server_id" value="{{ $serverId }}">@endif
                <label for="promo_code" class="label">Kode promo (opsional)</label>
                <div class="flex gap-2">
                    <input id="promo_code" name="promo_code" value="{{ $promoCode ?? request('promo_code') }}" class="input uppercase" maxlength="32" autocomplete="off">
                    <button type="submit" class="btn-secondary shrink-0">Terapkan</button>
                </div>
                @if ($promoError)<p class="field-error">{{ $promoError }}</p>@endif
                @if ($promoCode)<p class="mt-1.5 text-sm text-emerald-300">Kode {{ $promoCode }} diterapkan: hemat {{ rupiah($discount) }}.</p>@endif
            </form>

            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6" data-once>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="player_id" value="{{ $playerId }}">
                @if ($serverId)<input type="hidden" name="server_id" value="{{ $serverId }}">@endif
                @if ($promoCode)<input type="hidden" name="promo_code" value="{{ $promoCode }}">@endif
                <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                <fieldset class="card">
                    <legend class="px-1 text-lg font-semibold text-white">Metode pembayaran</legend>
                    @if (is_simulation())
                        <p class="mb-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">SIMULASI / DEMO: tidak ada pembayaran nyata yang diproses.</p>
                    @endif
                    <div class="space-y-2">
                        @foreach ($methods as $key => $method)
                            <label class="block cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $key }}" class="peer sr-only" required @checked(old('payment_method', array_key_first($methods)) === $key)>
                                <span class="block rounded-lg border border-line bg-ink p-3 transition-colors peer-checked:border-brand-500 peer-checked:bg-brand-600/10 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
                                    <span class="block text-sm font-semibold text-white">{{ $method['label'] }}{{ is_simulation() ? ' (Simulasi)' : '' }}</span>
                                    <span class="block text-xs text-slate-400">{{ $method['hint'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method')<p class="field-error">{{ $message }}</p>@enderror
                </fieldset>

                @if (setting('order_notice'))<p class="text-sm text-slate-400">{{ setting('order_notice') }}</p>@endif

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">Buat Pesanan</button>
                    <a href="{{ route('games.show', $game) }}" class="btn-secondary">Ubah pilihan</a>
                </div>
            </form>
        </div>

        <aside class="card h-fit" aria-label="Ringkasan pesanan">
            <h2 class="text-lg">Ringkasan</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Game</dt><dd class="text-right text-white">{{ $game->name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Produk</dt><dd class="text-right text-white">{{ $product->name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Player ID</dt><dd class="break-all text-right text-white">{{ $playerId }}</dd></div>
                @if ($serverId)<div class="flex justify-between gap-3"><dt class="text-slate-400">Server ID</dt><dd class="text-right text-white">{{ $serverId }}</dd></div>@endif
                <hr class="border-line">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">Harga</dt><dd>{{ rupiah($subtotal) }}</dd></div>
                @if ($discount > 0)<div class="flex justify-between gap-3 text-emerald-300"><dt>Diskon</dt><dd>- {{ rupiah($discount) }}</dd></div>@endif
                <div class="flex justify-between gap-3 text-base font-semibold text-white"><dt>Total</dt><dd>{{ rupiah($total) }}</dd></div>
            </dl>
            <p class="hint">Harga final dihitung ulang oleh server saat pesanan dibuat.</p>
        </aside>
    </div>
</section>
@endsection
