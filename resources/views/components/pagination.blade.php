{{-- Pagination Komponenti --}}
@props(['paginator'])

@if ($paginator->hasPages())
<nav aria-label="Sayfa navigasyonu">
    <ul class="pagination justify-content-center">
        {{-- Önceki Sayfa --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">&laquo; Önceki</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Önceki</a>
            </li>
        @endif

        {{-- Sayfa Numaraları --}}
        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $page }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endif
        @endforeach

        {{-- Sonraki Sayfa --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Sonraki &raquo;</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">Sonraki &raquo;</span>
            </li>
        @endif
    </ul>

    {{-- Sayfa Bilgisi --}}
    <div class="text-center text-muted small mt-2">
        {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} arası gösteriliyor (Toplam: {{ $paginator->total() }})
    </div>
</nav>
@endif
