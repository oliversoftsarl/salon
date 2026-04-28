@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex align-items-center justify-content-center gap-2 flex-wrap py-2">
        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
            <button class="btn btn-sm btn-outline-secondary" disabled style="min-width: 32px;">
                <i class="ni ni-fat-back"></i>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary" style="min-width: 32px;">
                <i class="ni ni-fat-back"></i>
            </a>
        @endif

        {{-- Numéros de page --}}
        @php
            $from = max($paginator->currentPage() - 1, 1);
            $to = min($paginator->currentPage() + 1, $paginator->lastPage());
        @endphp

        @if ($from > 1)
            <a href="{{ $paginator->url(1) }}" class="btn btn-sm btn-outline-secondary" style="min-width: 32px;">1</a>
            @if ($from > 2)
                <span class="text-muted small" style="padding: 0.375rem 0.25rem;">...</span>
            @endif
        @endif

        @for ($i = $from; $i <= $to; $i++)
            @if ($i == $paginator->currentPage())
                <button class="btn btn-sm btn-dark" disabled style="min-width: 32px;">{{ $i }}</button>
            @else
                <a href="{{ $paginator->url($i) }}" class="btn btn-sm btn-outline-secondary" style="min-width: 32px;">{{ $i }}</a>
            @endif
        @endfor

        @if ($to < $paginator->lastPage())
            @if ($to < $paginator->lastPage() - 1)
                <span class="text-muted small" style="padding: 0.375rem 0.25rem;">...</span>
            @endif
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="btn btn-sm btn-outline-secondary" style="min-width: 32px;">{{ $paginator->lastPage() }}</a>
        @endif

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary" style="min-width: 32px;">
                <i class="ni ni-fat-forward"></i>
            </a>
        @else
            <button class="btn btn-sm btn-outline-secondary" disabled style="min-width: 32px;">
                <i class="ni ni-fat-forward"></i>
            </button>
        @endif

        {{-- Infos pagination --}}
        <span class="text-muted small ms-2" style="white-space: nowrap;">
            {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
            @if($paginator->total())
                • {{ $paginator->total() }}
            @endif
        </span>
    </nav>
@endif

