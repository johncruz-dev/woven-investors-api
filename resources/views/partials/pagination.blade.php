@props([
    'paginator',
    'perPageOptions' => [10, 20, 50],
])

@if ($paginator->total() > 0)
    <div class="table-footer pagination-footer">
        <div class="pagination-summary muted">
            Showing
            <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
        </div>

        <form method="GET" class="per-page-wrap">
            @foreach (request()->except(['page', 'per_page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="per-page-select">Rows</label>
            <select id="per-page-select" name="per_page" onchange="this.form.submit()">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) $paginator->perPage() === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>

        <nav class="pagination-nav" aria-label="Pagination">
            @if ($paginator->onFirstPage())
                <span class="page-btn nav-btn" aria-disabled="true">‹</span>
            @else
                <a class="page-btn nav-btn" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous page">‹</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span class="page-btn active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-btn nav-btn" href="{{ $paginator->nextPageUrl() }}" aria-label="Next page">›</a>
            @else
                <span class="page-btn nav-btn" aria-disabled="true">›</span>
            @endif
        </nav>
    </div>
@endif
