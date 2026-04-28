@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex align-items-center justify-content-center gap-2 flex-wrap py-2">
        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled style="min-width: 32px;" aria-disabled="true" aria-label="Précédent">
                <i class="ni ni-fat-back"></i>
            </button>
        @else
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                style="min-width: 32px;"
                wire:click="previousPage('{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
                aria-label="Précédent"
            >
                <i class="ni ni-fat-back"></i>
            </button>
        @endif

        {{-- Numéros de page (compact) --}}
        @php
            $from = max($paginator->currentPage() - 1, 1);
            $to = min($paginator->currentPage() + 1, $paginator->lastPage());
        @endphp

        @if ($from > 1)
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                style="min-width: 32px;"
                wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
            >1</button>

            @if ($from > 2)
                <span class="text-muted small" style="padding: 0.375rem 0.25rem;">...</span>
            @endif
        @endif

        @for ($i = $from; $i <= $to; $i++)
            @if ($i == $paginator->currentPage())
                <button type="button" class="btn btn-sm btn-dark" disabled style="min-width: 32px;" aria-current="page">{{ $i }}</button>
            @else
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    style="min-width: 32px;"
                    wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                >{{ $i }}</button>
            @endif
        @endfor

        @if ($to < $paginator->lastPage())
            @if ($to < $paginator->lastPage() - 1)
                <span class="text-muted small" style="padding: 0.375rem 0.25rem;">...</span>
            @endif

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                style="min-width: 32px;"
                wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
            >{{ $paginator->lastPage() }}</button>
        @endif

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                style="min-width: 32px;"
                wire:click="nextPage('{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
                aria-label="Suivant"
            >
                <i class="ni ni-fat-forward"></i>
            </button>
        @else
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled style="min-width: 32px;" aria-disabled="true" aria-label="Suivant">
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

