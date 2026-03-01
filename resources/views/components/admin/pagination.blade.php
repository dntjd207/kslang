@if ($paginator->hasPages())
    <nav class="flex items-center justify-between mt-6">
        <div class="text-sm text-gray-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / 총 {{ $paginator->total() }}건
        </div>
        <div class="flex space-x-1">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">&laquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">&laquo;</a>
            @endif

            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="px-3 py-2 text-sm text-white bg-indigo-600 rounded-md">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">&raquo;</a>
            @else
                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">&raquo;</span>
            @endif
        </div>
    </nav>
@endif
