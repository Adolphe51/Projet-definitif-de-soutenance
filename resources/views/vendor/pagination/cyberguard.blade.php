@if ($paginator->hasPages())
    <nav class="cg-pagination" role="navigation" aria-label="Pagination">
        <div class="cg-pagination__meta">
            <span class="cg-pagination__summary">
                Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }}
            </span>
            <span class="cg-pagination__summary">
                {{ number_format($paginator->firstItem() ?? 0) }}-{{ number_format($paginator->lastItem() ?? 0) }}
                sur {{ number_format($paginator->total()) }} élément(s)
            </span>
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

            <div class="cg-pagination__pages">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="cg-pagination__ellipsis" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="cg-pagination__page cg-pagination__page--active" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="cg-pagination__page" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

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
