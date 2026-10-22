@props(['color' => 'gray'])
@php
    $classes = match ($color) {
        'green' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30',
        'yellow' => 'bg-amber-500/15 text-amber-300 ring-amber-500/30',
        'blue' => 'bg-sky-500/15 text-sky-300 ring-sky-500/30',
        'red' => 'bg-red-500/15 text-red-300 ring-red-500/30',
        'purple' => 'bg-purple-500/15 text-purple-300 ring-purple-500/30',
        default => 'bg-slate-500/15 text-slate-300 ring-slate-500/30',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>{{ $slot }}</span>
