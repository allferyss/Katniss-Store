@props(['title', 'text' => null])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-line px-6 py-12 text-center']) }}>
    <p class="text-base font-semibold text-white">{{ $title }}</p>
    @if ($text)<p class="mx-auto mt-1 max-w-md text-sm text-slate-400">{{ $text }}</p>@endif
    @if (trim((string) $slot) !== '')<div class="mt-4">{{ $slot }}</div>@endif
</div>
