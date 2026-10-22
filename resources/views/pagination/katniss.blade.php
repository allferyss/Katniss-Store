@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-400">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}</p>
        <ul class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <li><span class="pg pg-disabled" aria-disabled="true">‹</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pg" aria-label="Halaman sebelumnya">‹</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="pg pg-disabled" aria-disabled="true">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="pg pg-active" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="pg" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pg" aria-label="Halaman berikutnya">›</a></li>
            @else
                <li><span class="pg pg-disabled" aria-disabled="true">›</span></li>
            @endif
        </ul>
    </nav>
@endif
