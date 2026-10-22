@props(['game'])
<a href="{{ route('games.show', $game) }}" class="group block overflow-hidden rounded-xl border border-line bg-surface transition-colors hover:border-brand-500">
    <div class="aspect-[4/3] overflow-hidden bg-surface2">
        <img src="{{ $game->image_url }}" alt="{{ $game->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
    </div>
    <div class="p-4">
        <h3 class="truncate text-base">{{ $game->name }}</h3>
        @if ($game->publisher)<p class="truncate text-xs text-slate-400">{{ $game->publisher }}</p>@endif
        <p class="mt-2 text-sm text-slate-300">
            @if ($game->min_price)
                Mulai <span class="font-semibold text-brand-300">{{ rupiah($game->min_price) }}</span>
            @else
                <span class="text-slate-500">Belum ada produk</span>
            @endif
        </p>
    </div>
</a>
