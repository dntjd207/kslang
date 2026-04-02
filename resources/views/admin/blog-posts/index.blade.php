@extends('layouts.admin')

@section('title', '블로그 글 관리 - kslang Admin')

@php
    $statusBadgeClasses = [
        \App\Models\BlogPost::STATUS_DRAFT => 'bg-amber-100 text-amber-800',
        \App\Models\BlogPost::STATUS_PUBLISHED => 'bg-emerald-100 text-emerald-800',
        \App\Models\BlogPost::STATUS_ARCHIVED => 'bg-slate-200 text-slate-700',
    ];

    $translationBadgeClasses = [
        \App\Models\BlogPost::TRANSLATION_NONE => 'bg-slate-100 text-slate-700',
        \App\Models\BlogPost::TRANSLATION_SYNCED => 'bg-blue-100 text-blue-800',
        \App\Models\BlogPost::TRANSLATION_OUTDATED => 'bg-rose-100 text-rose-800',
    ];

    $statusOptions = [
        \App\Models\BlogPost::STATUS_DRAFT => '임시 저장',
        \App\Models\BlogPost::STATUS_PUBLISHED => '발행됨',
        \App\Models\BlogPost::STATUS_ARCHIVED => '보관됨',
    ];

    $translationOptions = [
        \App\Models\BlogPost::TRANSLATION_NONE => '영문 없음',
        \App\Models\BlogPost::TRANSLATION_SYNCED => '영문 최신',
        \App\Models\BlogPost::TRANSLATION_OUTDATED => '재번역 필요',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">블로그 글 관리</h2>
                <p class="mt-1 text-sm text-gray-500">영문 SEO용 블로그 글을 임시 저장하고 발행할 수 있습니다.</p>
            </div>

            <a href="{{ route('admin.blog-posts.create') }}">
                <x-common.button>
                    새 블로그 글 작성
                </x-common.button>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-common.card>
                <p class="text-sm font-medium text-gray-500">임시 저장</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $statusCounts[\App\Models\BlogPost::STATUS_DRAFT] ?? 0 }}</p>
            </x-common.card>

            <x-common.card>
                <p class="text-sm font-medium text-gray-500">발행됨</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $statusCounts[\App\Models\BlogPost::STATUS_PUBLISHED] ?? 0 }}</p>
            </x-common.card>

            <x-common.card>
                <p class="text-sm font-medium text-gray-500">재번역 필요</p>
                <p class="mt-2 text-3xl font-bold text-rose-600">{{ $translationCounts[\App\Models\BlogPost::TRANSLATION_OUTDATED] ?? 0 }}</p>
            </x-common.card>
        </div>

        <x-common.card>
            <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_180px_180px_auto]">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="제목, 키워드, slug"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">전체</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="translation_status" class="block text-sm font-medium text-gray-700 mb-1">번역 상태</label>
                    <select
                        name="translation_status"
                        id="translation_status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">전체</option>
                        @foreach ($translationOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('translation_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                    <select
                        name="category"
                        id="category"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">전체</option>
                        @foreach ($categoryOptions as $categoryOption)
                            <option value="{{ $categoryOption }}" @selected(request('category') === $categoryOption)>{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tag" class="block text-sm font-medium text-gray-700 mb-1">태그</label>
                    <select
                        name="tag"
                        id="tag"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">전체</option>
                        @foreach ($tagOptions as $tagOption)
                            <option value="{{ $tagOption }}" @selected(request('tag') === $tagOption)>{{ $tagOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-common.button type="submit" variant="secondary">
                        필터 적용
                    </x-common.button>
                    @if (request()->filled('search') || request()->filled('status') || request()->filled('translation_status') || request()->filled('category') || request()->filled('tag'))
                        <a href="{{ route('admin.blog-posts.index') }}">
                            <x-common.button type="button" variant="secondary">
                                초기화
                            </x-common.button>
                        </a>
                    @endif
                </div>
            </form>
        </x-common.card>

        <x-common.card :padding="false">
            @if ($blogPosts->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-lg font-semibold text-gray-700">아직 작성된 블로그 글이 없습니다.</p>
                    <p class="mt-2 text-sm text-gray-500">첫 번째 SEO 글을 작성해서 검색 유입 구조를 시작해보세요.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">글 정보</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">상태</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SEO</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">업데이트</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">액션</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($blogPosts as $blogPost)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4 align-top">
                                        <div class="max-w-xl">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $blogPost->title_ko ?: '제목 없음' }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $blogPost->title_en ?: '영문 제목 없음' }}
                                            </p>
                                            <p class="mt-2 text-xs text-gray-400">
                                                /blog/{{ $blogPost->slug }}
                                            </p>
                                            @if ($blogPost->primary_keyword)
                                                <p class="mt-2 text-xs font-medium text-indigo-600">
                                                    키워드: {{ $blogPost->primary_keyword }}
                                                </p>
                                            @endif
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if ($blogPost->category_name)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                                        {{ $blogPost->category_name }}
                                                    </span>
                                                @endif
                                                @foreach ($blogPost->tags_list as $tag)
                                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$blogPost->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $blogPost->status_label }}
                                            </span>
                                            <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $translationBadgeClasses[$blogPost->translation_status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $blogPost->translation_status_label }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                관련 슬랭 {{ $blogPost->slangs_count }}개
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="space-y-2">
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ $blogPost->seo_title_en ?: 'SEO 제목 자동 fallback' }}
                                            </p>
                                            <p class="text-xs leading-5 text-gray-500">
                                                {{ $blogPost->resolved_seo_description ?: 'SEO 설명 자동 fallback' }}
                                            </p>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top text-sm text-gray-500">
                                        <p>수정: {{ $blogPost->updated_at?->format('Y-m-d H:i') }}</p>
                                        <p class="mt-1">발행: {{ $blogPost->published_at?->format('Y-m-d H:i') ?? '-' }}</p>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($blogPost->isPublished())
                                                <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" target="_blank">
                                                    <x-common.button type="button" variant="secondary" size="sm">
                                                        공개 보기
                                                    </x-common.button>
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.blog-posts.edit', $blogPost) }}">
                                                <x-common.button type="button" variant="secondary" size="sm">
                                                    수정
                                                </x-common.button>
                                            </a>

                                            <form method="POST" action="{{ route('admin.blog-posts.destroy', $blogPost) }}" onsubmit="return confirm('이 블로그 글을 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-common.button type="submit" variant="danger" size="sm">
                                                    삭제
                                                </x-common.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $blogPosts->links() }}
                </div>
            @endif
        </x-common.card>
    </div>
@endsection
