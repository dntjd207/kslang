@extends($layout)

@section('title', 'Privacy Policy - kslang')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">
        Privacy Policy
    </h1>

    <p class="text-sm text-gray-500 text-center mb-8">
        Effective Date: March 1, 2026
    </p>

    <hr class="mb-8 border-gray-200">

    <div class="prose prose-gray max-w-none
                prose-headings:text-gray-900
                prose-p:text-gray-700 prose-p:leading-relaxed
                prose-a:text-indigo-600 prose-a:underline
                prose-li:text-gray-700
                prose-table:border-collapse
                prose-th:bg-gray-50 prose-th:border prose-th:border-gray-300 prose-th:px-3 prose-th:py-2
                prose-td:border prose-td:border-gray-300 prose-td:px-3 prose-td:py-2">

        @include('public.privacy-content')

    </div>
</div>
@endsection
