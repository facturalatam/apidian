{{--
    Paginación compacta (Bootstrap 4): flechas en lugar de "Anterior/Siguiente",
    números de página y tamaño reducido. Se usa con $documents->links('partials.pagination').
--}}
@if ($paginator->hasPages())
    <nav aria-label="Paginación">
        <ul class="pagination pagination-sm mb-0 doc-pagination">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"><i class="fas fa-angle-left"></i></a></li>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
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

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente"><i class="fas fa-angle-right"></i></a></li>
            @else
                <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>
            @endif
        </ul>
    </nav>
@endif

@once
<style>
    .doc-pagination { margin-bottom: 0; }
    .doc-pagination .page-link {
        color: #2563eb; min-width: 34px; text-align: center;
        display: flex; align-items: center; justify-content: center;
    }
    .doc-pagination .page-item.active .page-link { background-color: #2563eb; border-color: #2563eb; color: #fff; }
    .doc-pagination .page-item.disabled .page-link { color: #adb5bd; }
</style>
@endonce
