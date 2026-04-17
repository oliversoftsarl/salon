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
            {{-- Mobile: Précédent / Suivant + info --}}
            <div class="d-flex d-sm-none justify-content-between align-items-center px-2">
                @if ($paginator->onFirstPage())
                    <span class="btn btn-sm btn-outline-secondary disabled">
                        <i class="ni ni-bold-left me-1"></i> Préc.
                    </span>
                @else
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled">
                        <i class="ni ni-bold-left me-1"></i> Préc.
                    </button>
                @endif

                <span class="text-xs text-secondary">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled">
                        Suiv. <i class="ni ni-bold-right ms-1"></i>
                    </button>
                @else
                    <span class="btn btn-sm btn-outline-secondary disabled">
                        Suiv. <i class="ni ni-bold-right ms-1"></i>
                    </span>
                @endif
            </div>

            {{-- Desktop --}}
            <div class="d-none d-sm-flex align-items-center justify-content-between px-2">
                <div>
                    <p class="text-xs text-secondary mb-0">
                        Affichage de
                        <span class="font-weight-bold">{{ $paginator->firstItem() }}</span>
                        à
                        <span class="font-weight-bold">{{ $paginator->lastItem() }}</span>
                        sur
                        <span class="font-weight-bold">{{ $paginator->total() }}</span>
                        résultats
                    </p>
                </div>

                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link border-0 bg-transparent" aria-hidden="true">
                                <i class="ni ni-bold-left text-xs"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <button type="button" class="page-link border-0 bg-transparent"
                                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    wire:loading.attr="disabled"
                                    aria-label="Précédent">
                                <i class="ni ni-bold-left text-xs"></i>
                            </button>
                        </li>
                    @endif

                    {{-- Pages --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-transparent text-xs">{{ $element }}</span>
                            </li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                        <span class="page-link border-0 rounded-circle text-xs text-white bg-gradient-primary" style="min-width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                        <button type="button" class="page-link border-0 bg-transparent text-xs"
                                                style="min-width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;"
                                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                x-on:click="{{ $scrollIntoViewJsSnippet }}">
                                            {{ $page }}
                                        </button>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button type="button" class="page-link border-0 bg-transparent"
                                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    wire:loading.attr="disabled"
                                    aria-label="Suivant">
                                <i class="ni ni-bold-right text-xs"></i>
                            </button>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link border-0 bg-transparent" aria-hidden="true">
                                <i class="ni ni-bold-right text-xs"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>
    @endif
</div>
