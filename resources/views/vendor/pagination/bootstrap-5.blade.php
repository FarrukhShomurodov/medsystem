{{-- Pagination of the dashboard lists: {{ $records->links() }} (AppServiceProvider makes this view the default). --}}
@if ($paginator->hasPages())
    <nav class="d-flex flex-wrap align-items-center gap-2" aria-label="Страницы списка">
        <p class="m-0 text-secondary small">
            Показаны <span class="fw-medium">{{ $paginator->firstItem() }}</span>–<span class="fw-medium">{{ $paginator->lastItem() }}</span>
            из <span class="fw-medium">{{ $paginator->total() }}</span>
        </p>

        <ul class="pagination m-0 ms-auto">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true"><i class="bx bx-chevron-left"></i></span>
                    <span class="visually-hidden">{{ __('pagination.previous') }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                        <i class="bx bx-chevron-left" aria-hidden="true"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                        <i class="bx bx-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true"><i class="bx bx-chevron-right"></i></span>
                    <span class="visually-hidden">{{ __('pagination.next') }}</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
