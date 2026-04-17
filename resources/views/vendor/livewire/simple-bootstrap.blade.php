@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav aria-label="Pagination">
            <div class="d-flex justify-content-between align-items-center px-2">
                @if ($paginator->onFirstPage())
                    <span class="btn btn-sm btn-outline-secondary disabled">
                        <i class="ni ni-bold-left me-1"></i> Précédent
                    </span>
                @else
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled">
                        <i class="ni ni-bold-left me-1"></i> Précédent
                    </button>
                @endif

                @if ($paginator->hasMorePages())
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled">
                        Suivant <i class="ni ni-bold-right ms-1"></i>
                    </button>
                @else
                    <span class="btn btn-sm btn-outline-secondary disabled">
                        Suivant <i class="ni ni-bold-right ms-1"></i>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
