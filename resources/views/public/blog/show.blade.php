@extends('layouts.public')

@section('title', $blogPost->resolved_seo_title . ' | kslang')

@php
    $schemaContextKey = '@'.'context';
    $schemaTypeKey = '@'.'type';
@endphp

@section('head')
    <link rel="canonical" href="{{ $blogPost->canonical_url ?: route('blog.show', ['blogPost' => $blogPost->slug]) }}">
@endsection

@section('meta')
    <meta name="description" content="{{ $blogPost->resolved_seo_description }}">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ $blogPost->resolved_seo_title }}">
    <meta property="og:description" content="{{ $blogPost->resolved_seo_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $blogPost->resolved_seo_title }}">
    <meta name="twitter:description" content="{{ $blogPost->resolved_seo_description }}">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode([
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'Article',
        'headline' => $blogPost->public_title,
        'description' => $blogPost->resolved_seo_description,
        'datePublished' => $blogPost->published_at?->toIso8601String(),
        'dateModified' => $blogPost->updated_at?->toIso8601String(),
        'mainEntityOfPage' => route('blog.show', ['blogPost' => $blogPost->slug]),
        'author' => [
            $schemaTypeKey => 'Organization',
            'name' => 'kslang',
        ],
        'publisher' => [
            $schemaTypeKey => 'Organization',
            'name' => 'kslang',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode([
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'BreadcrumbList',
        'itemListElement' => [
            [
                $schemaTypeKey => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => route('landing'),
            ],
            [
                $schemaTypeKey => 'ListItem',
                'position' => 2,
                'name' => 'Blog',
                'item' => route('blog.index'),
            ],
            [
                $schemaTypeKey => 'ListItem',
                'position' => 3,
                'name' => $blogPost->public_title,
                'item' => route('blog.show', ['blogPost' => $blogPost->slug]),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}
@endsection

@section('content')
    @php
        $hasMetaChips = $blogPost->category_name || $blogPost->tags_list !== [] || $blogPost->slangs->isNotEmpty();
    @endphp

    <article class="bg-white">
        <header class="border-b border-gray-200 bg-gradient-to-b from-fuchsia-50 via-white to-white">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <nav class="text-sm text-gray-500">
                    <a href="{{ route('landing') }}" class="transition hover:text-fuchsia-600">Home</a>
                    <span class="mx-2 text-gray-300">/</span>
                    <a href="{{ route('blog.index') }}" class="transition hover:text-fuchsia-600">Blog</a>
                </nav>

                <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                    <span>{{ $blogPost->published_at?->format('F j, Y') }}</span>
                    <span>&middot;</span>
                    <span>{{ $blogPost->reading_time_minutes }} min read</span>
                    @if ($blogPost->primary_keyword)
                        <span class="rounded-full bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700">
                            {{ $blogPost->primary_keyword }}
                        </span>
                    @endif
                </div>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                    {{ $blogPost->public_title }}
                </h1>

                @if ($blogPost->public_excerpt)
                    <p class="mt-5 text-lg leading-8 text-gray-600">
                        {{ $blogPost->public_excerpt }}
                    </p>
                @endif

                @if ($hasMetaChips)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @if ($blogPost->category_name)
                            <a href="{{ route('blog.index', ['category' => $blogPost->category_name]) }}"
                               class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                {{ $blogPost->category_name }}
                            </a>
                        @endif

                        @foreach ($blogPost->tags_list as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                               class="rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-semibold text-fuchsia-700 transition hover:bg-fuchsia-100">
                                {{ $tag }}
                            </a>
                        @endforeach

                        @foreach ($blogPost->slangs as $slang)
                            <a href="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}"
                               class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 transition hover:bg-fuchsia-50 hover:text-fuchsia-700">
                                {{ $slang->korean }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </header>

        <div class="mx-auto grid max-w-6xl grid-cols-1 gap-12 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:px-8">
            <div class="min-w-0">
                <div class="mb-8 rounded-[2rem] border border-fuchsia-200 bg-gradient-to-r from-fuchsia-50 via-white to-cyan-50 p-6 shadow-sm">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-600">Study smarter</p>
                            <h2 class="mt-2 text-2xl font-bold text-gray-900">Want quick lookup while you read?</h2>
                            <p class="mt-3 text-sm leading-7 text-gray-600">
                                Open kslang on mobile to review pronunciation, example-based learning, and related slang without leaving your study flow.
                            </p>
                        </div>

                        @if ($playStoreUrl)
                            <a
                                href="{{ $playStoreUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                data-cta-track
                                data-cta-target="google_play"
                                data-cta-source-type="blog_show"
                                data-cta-placement="inline_top"
                                data-cta-blog-post-id="{{ $blogPost->id }}"
                                class="inline-flex items-center rounded-full bg-fuchsia-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700"
                            >
                                Download on Google Play
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="prose prose-gray max-w-none prose-headings:text-gray-900 prose-headings:scroll-mt-24 prose-p:text-gray-700 prose-p:leading-relaxed prose-a:text-fuchsia-600 prose-a:underline prose-li:text-gray-700 prose-strong:text-gray-900 prose-blockquote:border-fuchsia-200 prose-blockquote:text-gray-600 prose-th:border prose-th:border-gray-300 prose-th:bg-gray-50 prose-td:border prose-td:border-gray-300">
                    {!! $bodyHtml !!}
                    </div>
                </div>

                @if ($playStoreUrl)
                    <div class="mt-8 rounded-[2rem] border border-gray-200 bg-gray-50 p-6 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-600">Next Step</p>
                                <h2 class="mt-2 text-2xl font-bold text-gray-900">Keep learning inside the app.</h2>
                                <p class="mt-2 text-sm leading-7 text-gray-600">
                                    Save time by checking pronunciation, related slang, and curated examples while reading English guides.
                                </p>
                            </div>

                            <a
                                href="{{ $playStoreUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                data-cta-track
                                data-cta-target="google_play"
                                data-cta-source-type="blog_show"
                                data-cta-placement="inline_bottom"
                                data-cta-blog-post-id="{{ $blogPost->id }}"
                                class="inline-flex items-center rounded-full bg-fuchsia-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700"
                            >
                                Download on Google Play
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                @if (!empty($tocItems))
                    <nav class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-600">Table of Contents</h2>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($tocItems as $tocItem)
                                <li class="{{ $tocItem['level'] === 3 ? 'pl-4' : '' }}">
                                    <a href="#{{ $tocItem['id'] }}"
                                       class="block leading-snug text-gray-600 transition hover:text-fuchsia-600">
                                        {{ $tocItem['text'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-600">Explore More</p>
                    <h2 class="mt-3 text-xl font-bold text-gray-900">Related slang pages</h2>
                    @if ($blogPost->slangs->isEmpty())
                        <p class="mt-3 text-sm leading-6 text-gray-500">No related slang pages are linked yet.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($blogPost->slangs as $slang)
                                <a href="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}" class="block rounded-2xl border border-gray-200 px-4 py-3 transition hover:border-fuchsia-200 hover:bg-fuchsia-50/50">
                                    <p class="font-semibold text-gray-900">{{ $slang->korean }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $slang->public_summary }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-fuchsia-200 bg-fuchsia-50 p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-fuchsia-700">App</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900">Learn faster inside kslang.</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Check pronunciation, examples, and related slang in one place while you study.
                    </p>
                    @if ($playStoreUrl)
                        <a
                            href="{{ $playStoreUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-cta-track
                            data-cta-target="google_play"
                            data-cta-source-type="blog_show"
                            data-cta-placement="sidebar"
                            data-cta-blog-post-id="{{ $blogPost->id }}"
                            class="mt-5 inline-flex items-center rounded-full bg-fuchsia-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700"
                        >
                            Download on Google Play
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <section class="border-t border-gray-200 bg-gray-50">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-fuchsia-600">More Reading</p>
                        <h2 class="mt-2 text-3xl font-bold text-gray-900">Related articles</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-fuchsia-600 transition hover:text-fuchsia-700">
                        View all
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    @foreach ($relatedPosts as $relatedPost)
                        <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <p class="text-sm text-gray-500">{{ $relatedPost->published_at?->format('M j, Y') }}</p>
                            <h3 class="mt-3 text-xl font-bold text-gray-900">
                                <a href="{{ route('blog.show', ['blogPost' => $relatedPost->slug]) }}" class="transition hover:text-fuchsia-600">
                                    {{ $relatedPost->public_title }}
                                </a>
                            </h3>
                            @if ($relatedPost->public_excerpt)
                                <p class="mt-3 text-sm leading-6 text-gray-600">
                                    {{ $relatedPost->public_excerpt }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
