@extends($layout)

@section('title', $page->title . ' - kslang')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">
        {{ $page->title }}
    </h1>

    @if ($page->updated_at)
        <p class="text-sm text-gray-500 text-center mb-8">
            Last updated: {{ $page->updated_at->format('F j, Y') }}
        </p>
    @endif

    <hr class="mb-8 border-gray-200">

    @if ($page->content)
        <div class="prose prose-gray max-w-none
                    prose-headings:text-gray-900
                    prose-p:text-gray-700 prose-p:leading-relaxed
                    prose-a:text-indigo-600 prose-a:underline
                    prose-li:text-gray-700
                    prose-table:border-collapse
                    prose-th:bg-gray-50 prose-th:border prose-th:border-gray-300 prose-th:px-3 prose-th:py-2
                    prose-td:border prose-td:border-gray-300 prose-td:px-3 prose-td:py-2">
            {!! $page->content !!}
        </div>
    @else
        <div class="text-center py-16">
            <p class="text-gray-400 text-lg">내용이 아직 작성되지 않았습니다.</p>
        </div>
    @endif
</div>
@endsection
