@extends('layouts.admin')

@section('title', 'CTA 클릭 집계 - kslang Admin')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @php
            $ranges = ['1d' => '오늘', '7d' => '7일', '30d' => '30일', '90d' => '90일', 'all' => '전체'];
        @endphp
        @foreach ($ranges as $key => $label)
            <a href="{{ route('admin.cta-clicks.index', ['range' => $key]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $range === $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-300' }}">
                {{ $label }}
            </a>
        @endforeach

        <span class="ml-auto text-sm text-gray-500">
            {{ $startDate->format('Y-m-d') }} ~
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-8">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">총 클릭 수</p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalClicks) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">출처 유형 수</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $clicksBySourceType->count() }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">위치 유형 수</p>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $clicksByPlacement->count() }}</p>
        </div>
    </div>

    @if ($clicksByDate->isNotEmpty())
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">일별 클릭 추이</h2>
            <div class="mt-4 overflow-x-auto">
                <div class="flex items-end gap-1" style="min-height: 160px;">
                    @php
                        $maxCount = $clicksByDate->max() ?: 1;
                    @endphp
                    @foreach ($clicksByDate as $date => $count)
                        <div class="flex flex-col items-center gap-1" style="flex: 1; min-width: 28px;">
                            <span class="text-xs font-medium text-gray-700">{{ $count }}</span>
                            <div class="w-full rounded-t bg-indigo-500" style="height: {{ max(4, ($count / $maxCount) * 140) }}px;"></div>
                            <span class="text-[10px] text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($date)->format('m/d') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">출처 유형별 클릭</h2>
            @if ($clicksBySourceType->isEmpty())
                <p class="mt-4 text-sm text-gray-500">데이터 없음</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($clicksBySourceType as $sourceType => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-8 items-center rounded-full bg-indigo-50 px-3 text-xs font-semibold text-indigo-700">
                                    {{ $sourceType }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-2 rounded-full bg-indigo-200" style="width: {{ max(20, ($count / max(1, $totalClicks)) * 200) }}px;">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: 100%;"></div>
                                </div>
                                <span class="w-12 text-right text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">위치별 클릭</h2>
            @if ($clicksByPlacement->isEmpty())
                <p class="mt-4 text-sm text-gray-500">데이터 없음</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($clicksByPlacement as $placement => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-8 items-center rounded-full bg-emerald-50 px-3 text-xs font-semibold text-emerald-700">
                                    {{ $placement }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-2 rounded-full bg-emerald-200" style="width: {{ max(20, ($count / max(1, $totalClicks)) * 200) }}px;">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: 100%;"></div>
                                </div>
                                <span class="w-12 text-right text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Top 블로그 (CTA 클릭)</h2>
            @if ($topBlogPosts->isEmpty())
                <p class="mt-4 text-sm text-gray-500">데이터 없음</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($topBlogPosts as $item)
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm text-gray-700">
                                @if ($item->blogPost)
                                    {{ $item->blogPost->title_ko ?: $item->blogPost->title_en ?: '(제목 없음)' }}
                                @else
                                    <span class="text-gray-400">(삭제된 글)</span>
                                @endif
                            </p>
                            <span class="shrink-0 text-sm font-semibold text-gray-900">{{ number_format($item->count) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Top 슬랭 (CTA 클릭)</h2>
            @if ($topSlangs->isEmpty())
                <p class="mt-4 text-sm text-gray-500">데이터 없음</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($topSlangs as $item)
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm text-gray-700">
                                @if ($item->slang)
                                    {{ $item->slang->korean }}
                                @else
                                    <span class="text-gray-400">(삭제된 슬랭)</span>
                                @endif
                            </p>
                            <span class="shrink-0 text-sm font-semibold text-gray-900">{{ number_format($item->count) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">최근 클릭 이벤트</h2>
        </div>

        @if ($recentClicks->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-500">
                아직 기록된 클릭이 없습니다.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">시간</th>
                            <th class="px-4 py-3">출처</th>
                            <th class="px-4 py-3">위치</th>
                            <th class="px-4 py-3">관련 콘텐츠</th>
                            <th class="px-4 py-3">Referer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentClicks as $click)
                            <tr class="hover:bg-gray-50/50 transition-colors duration-100">
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">
                                    {{ $click->created_at->format('m/d H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                        {{ $click->source_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        {{ $click->placement }}
                                    </span>
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-gray-700">
                                    @if ($click->blogPost)
                                        {{ $click->blogPost->title_ko ?: $click->blogPost->slug }}
                                    @elseif ($click->slang)
                                        {{ $click->slang->korean }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-gray-400 text-xs">
                                    {{ $click->referer_url ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
