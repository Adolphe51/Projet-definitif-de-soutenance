@if ($paginator->hasPages())
    <nav class="cg-pagination cg-pagination--simple" role="navigation" aria-label="Pagination">
        <div class="cg-pagination__meta">
            <span class="cg-pagination__summary">Page {{ $paginator->currentPage() }}</span>
            <span class="cg-pagination__summary">Navigation simplifiée</span>
        </div>

        <div class="cg-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="cg-pagination__button cg-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    ‹ Précédent
                </span>
            @else
                <a class="cg-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    ‹ Précédent
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="cg-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                    Suivant ›
                </a>
            @else
                <span class="cg-pagination__button cg-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    Suivant ›
                </span>
            @endif
        </div>
    </nav>
@endif
