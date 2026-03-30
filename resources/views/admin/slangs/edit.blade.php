@extends('layouts.admin')

@section('title', '욕/슬랭 수정 - kslang Admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">욕/슬랭 수정</h2>
            <p class="mt-1 text-sm text-gray-500">현재 폼의 단어, 설명, 예문을 카드뉴스용 포맷으로 복사할 수 있습니다.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-common.button type="button" variant="secondary" size="sm" data-copy-card-news>
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2h-2m-6 4H6a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2z"/>
                </svg>
                카드뉴스용 복사
            </x-common.button>

            <a href="{{ route('admin.slangs.index') }}">
                <x-common.button variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    목록으로
                </x-common.button>
            </a>
        </div>
    </div>

    <form id="slang-form" action="{{ route('admin.slangs.update', $slang) }}" method="POST" enctype="multipart/form-data" data-slang-id="{{ $slang->id }}">
        @csrf
        @method('PUT')
        @include('admin.slangs._form')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        @include('admin.slangs._form_scripts')
    </script>
@endpush
