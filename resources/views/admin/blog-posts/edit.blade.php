@extends('layouts.admin')

@section('title', '블로그 글 수정 - kslang Admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">블로그 글 수정</h2>
            <p class="mt-1 text-sm text-gray-500">한국어 원본을 수정하면 영어 공개본은 재번역 필요 상태로 전환됩니다.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($blogPost->isPublished())
                <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" target="_blank">
                    <x-common.button variant="secondary" size="sm">
                        공개 보기
                    </x-common.button>
                </a>
            @endif

            <a href="{{ route('admin.blog-posts.index') }}">
                <x-common.button variant="secondary" size="sm">
                    목록으로
                </x-common.button>
            </a>
        </div>
    </div>

    <form
        id="blog-post-form"
        action="{{ route('admin.blog-posts.update', $blogPost) }}"
        method="POST"
        data-autosave-enabled="{{ $blogPost->status !== \App\Models\BlogPost::STATUS_PUBLISHED ? 'true' : 'false' }}"
        data-autosave-endpoint="{{ route('admin.blog-posts.autosave') }}"
    >
        @csrf
        @method('PUT')
        @include('admin.blog-posts._form')
    </form>
@endsection

@push('scripts')
    @include('admin.blog-posts._scripts')
@endpush
