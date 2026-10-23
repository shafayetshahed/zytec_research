@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-muted">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>
        <ul class="flex items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="btn btn-outline btn-sm opacity-50" aria-disabled="true">Previous</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">Previous</a>
                @endif
            </li>
            <li class="px-2 text-muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</li>
            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">Next</a>
                @else
                    <span class="btn btn-outline btn-sm opacity-50" aria-disabled="true">Next</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
