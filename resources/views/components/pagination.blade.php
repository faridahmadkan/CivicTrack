@props(['paginator' => null, 'class' => '', 'attributes' => []])

@if($paginator && $paginator->hasPages())
    <div class="pagination {{ $class }}" {{ $attributes }} role="navigation" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="pagination-link disabled" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" rel="prev" aria-label="Previous page">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif

        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page === $paginator->currentPage())
                <span class="pagination-link active" aria-current="page">{{ $page }}</span>
            @elseif(is_string($page))
                <span class="pagination-ellipsis" aria-hidden="true">...</span>
            @else
                <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" rel="next" aria-label="Next page">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="pagination-link disabled" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </div>
@endif