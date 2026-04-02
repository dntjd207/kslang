@extends('layouts.admin')

@section('title', '블로그 글 작성 - kslang Admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">블로그 글 작성</h2>
            <p class="mt-1 text-sm text-gray-500">한국어 기준으로 초안을 만들고 영어 공개본을 발행용으로 다듬습니다.</p>
        </div>

        <a href="{{ route('admin.blog-posts.index') }}">
            <x-common.button variant="secondary" size="sm">
                목록으로
            </x-common.button>
        </a>
    </div>

    <form
        id="blog-post-form"
        action="{{ route('admin.blog-posts.store') }}"
        method="POST"
        data-autosave-enabled="true"
        data-autosave-endpoint="{{ route('admin.blog-posts.autosave') }}"
    >
        @csrf
        @include('admin.blog-posts._form')
    </form>
@endsection

@push('scripts')
    @include('admin.blog-posts._scripts')
@endpush
