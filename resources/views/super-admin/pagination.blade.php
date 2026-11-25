@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="{{ __('Page navigation') }}">
        @if ($paginator->onFirstPage())
            <span class="admin-pagination-disabled" aria-disabled="true">{{ __('‹ Previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('‹ Previous') }}</a>
        @endif

        <span class="admin-pagination-count">{{ __('Page :current of :last', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('Next ›') }}</a>
        @else
            <span class="admin-pagination-disabled" aria-disabled="true">{{ __('Next ›') }}</span>
        @endif
    </nav>
@endif
