@extends('layouts.admin')

@section('title', $pageTitle)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h2>
        <a href="{{ route($page->slug === 'privacy' ? 'privacy' : 'terms') }}"
           target="_blank"
           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            공개 페이지 보기
        </a>
    </div>

    <form id="page-form"
          action="{{ route('admin.pages.update', $page->slug) }}"
          method="POST">
        @csrf
        @method('PUT')

        <x-common.card>
            @error('content')
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ $message }}</p>
                </div>
            @enderror

            <textarea id="content-editor"
                      name="content"
                      class="w-full">{!! old('content', $page->content) !!}</textarea>
        </x-common.card>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                @if ($page->updated_at)
                    마지막 수정: {{ $page->updated_at->format('Y년 m월 d일 H:i') }}
                @else
                    아직 수정된 적이 없습니다.
                @endif
            </div>

            <x-common.button variant="primary" type="submit">
                저장
            </x-common.button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .tox-tinymce { border-radius: 0.5rem !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
        selector: '#content-editor',
        height: 500,
        menubar: false,
        plugins: [
            'lists', 'link', 'image', 'table',
            'code', 'fullscreen', 'wordcount',
            'autolink', 'autoresize',
        ],
        toolbar: [
            'undo redo | blocks | bold italic underline strikethrough',
            'alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent',
            'link image table | removeformat | code fullscreen',
        ],
        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 16px;
                line-height: 1.6;
                color: #1f2937;
                max-width: 800px;
                margin: 0 auto;
                padding: 16px;
            }
            h1 { font-size: 2em; font-weight: bold; margin-bottom: 0.5em; }
            h2 { font-size: 1.5em; font-weight: bold; margin-bottom: 0.5em; }
            h3 { font-size: 1.25em; font-weight: bold; margin-bottom: 0.5em; }
            p { margin-bottom: 1em; }
            ul, ol { margin-left: 1.5em; margin-bottom: 1em; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #d1d5db; padding: 8px 12px; }
            th { background-color: #f3f4f6; font-weight: bold; }
            a { color: #4f46e5; text-decoration: underline; }
            blockquote { border-left: 4px solid #d1d5db; padding-left: 16px; color: #6b7280; }
        `,
        autoresize_min_height: 400,
        autoresize_max_height: 800,
        link_default_target: '_blank',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    document.getElementById('page-form').addEventListener('submit', function () {
        if (tinymce.activeEditor) {
            tinymce.activeEditor.save();
        }
    });
});
</script>
@endpush
