@extends('layouts.admin')

@section('title', '새 욕/슬랭 추가 - kslang Admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">새 욕/슬랭 추가</h2>
        <a href="{{ route('admin.slangs.index') }}">
            <x-common.button variant="secondary" size="sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                목록으로
            </x-common.button>
        </a>
    </div>

    <form action="{{ route('admin.slangs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.slangs._form')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        @include('admin.slangs._form_scripts')
    </script>
@endpush
