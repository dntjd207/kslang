@extends('layouts.public')

@section('title', 'Korean Slang Blog | kslang')

@php
    $schemaContextKey = '@'.'context';
    $schemaTypeKey = '@'.'type';
@endphp

@section('head')
    <link rel="canonical" href="{{ route('blog.index') }}">
@endsection

@section('meta')
    <meta name="description" content="Read English blog articles about Korean slang, internet buzzwords, usage nuance, and cultural context.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Korean Slang Blog | kslang">
    <meta property="og:description" content="English articles that explain Korean slang, nuance, and real usage context.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('blog.index') }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Korean Slang Blog | kslang">
    <meta name="twitter:description" content="English articles that explain Korean slang, nuance, and real usage context.">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode([
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'CollectionPage',
        'name' => 'Korean Slang Blog',
        'description' => 'English blog articles about Korean slang and cultural usage context.',
        'url' => route('blog.index'),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}
@endsection

@section('content')
    @php
        $featuredPost = $blogPosts->currentPage() === 1 ? $blogPosts->getCollection()->first() : null;
        $remainingPosts = $featuredPost
            ? $blogPosts->getCollection()->slice(1)->values()
            : $blogPosts->getCollection();
        $hasActiveFilters = $activeCategory !== null || $activeTag !== null;
    @endphp

    <section class="border-b border-gray-200 bg-gradient-to-b from-fuchsia-50 via-white to-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-fuchsia-600">Blog</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                English guides for Korean slang, meaning, nuance, and culture.
            </h1>
            <p class="mt-4 max-w-3xl text-lg leading-8 text-gray-600">
                Explore structured articles built for English readers who want to understand what Korean slang really means, when people use it, and when not to use it.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($hasActiveFilters)
            <div class="mb-8 flex flex-wrap items-center gap-2 rounded-3xl border border-fuchsia-200 bg-fuchsia-50/70 px-5 py-4 text-sm text-fuchsia-900">
                <span class="font-semibold">Active filters:</span>
                @if ($activeCategory)
                    <span class="rounded-full bg-white px-3 py-1 font-semibold text-fuchsia-700">Category: {{ $activeCategory }}</span>
                @endif
                @if ($activeTag)
                    <span class="rounded-full bg-white px-3 py-1 font-semibold text-fuchsia-700">Tag: {{ $activeTag }}</span>
                @endif
                <a href="{{ route('blog.index') }}" class="ml-auto text-sm font-semibold text-fuchsia-700 transition hover:text-fuchsia-900">
                    Clear filters
                </a>
            </div>
        @endif

        @if ($availableCategories->isNotEmpty() || $availableTags->isNotEmpty())
            <div class="mb-10 space-y-5">
                @if ($availableCategories->isNotEmpty())
                    <div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Categories</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('blog.index', array_filter(['tag' => $activeTag])) }}"
                               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeCategory === null ? 'bg-fuchsia-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-fuchsia-50 hover:text-fuchsia-700' }}">
                                All
                            </a>
                            @foreach ($availableCategories as $category)
                                <a href="{{ route('blog.index', array_filter(['category' => $category, 'tag' => $activeTag])) }}"
                                   class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeCategory === $category ? 'bg-fuchsia-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-fuchsia-50 hover:text-fuchsia-700' }}">
                                    {{ $category }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($availableTags->isNotEmpty())
                    <div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Tags</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('blog.index', array_filter(['category' => $activeCategory])) }}"
                               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeTag === null ? 'bg-fuchsia-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-fuchsia-50 hover:text-fuchsia-700' }}">
                                All
                            </a>
                            @foreach ($availableTags as $tag)
                                <a href="{{ route('blog.index', array_filter(['category' => $activeCategory, 'tag' => $tag])) }}"
                                   class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeTag === $tag ? 'bg-fuchsia-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-fuchsia-50 hover:text-fuchsia-700' }}">
                                    {{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if ($blogPosts->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900">No articles yet</h2>
                <p class="mt-3 text-gray-600">Blog posts will appear here once they are published.</p>
            </div>
        @else
            @if ($featuredPost)
                <article class="mb-10 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md sm:p-8">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span>{{ $featuredPost->published_at?->format('M j, Y') }}</span>
                        <span>&middot;</span>
                        <span>{{ $featuredPost->reading_time_minutes }} min read</span>
                        @if ($featuredPost->category_name)
                            <span class="rounded-full bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700">
                                {{ $featuredPost->category_name }}
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                        <a href="{{ route('blog.show', ['blogPost' => $featuredPost->slug]) }}" class="transition hover:text-fuchsia-600">
                            {{ $featuredPost->public_title }}
                        </a>
                    </h2>

                    @if ($featuredPost->public_excerpt)
                        <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
                            {{ $featuredPost->public_excerpt }}
                        </p>
                    @endif

                    @if ($featuredPost->tags_list !== [])
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($featuredPost->tags_list as $tag)
                                <a href="{{ route('blog.index', array_filter(['category' => $activeCategory, 'tag' => $tag])) }}"
                                   class="rounded-full bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700 transition hover:bg-fuchsia-100">
                                    {{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5">
                        <a href="{{ route('blog.show', ['blogPost' => $featuredPost->slug]) }}" class="inline-flex items-center text-sm font-semibold text-fuchsia-600 transition hover:text-fuchsia-700">
                            Read article &rarr;
                        </a>
                    </div>
                </article>
            @endif

            <div class="space-y-6">
                @foreach ($remainingPosts as $blogPost)
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                            <span>{{ $blogPost->published_at?->format('M j, Y') }}</span>
                            <span>&middot;</span>
                            <span>{{ $blogPost->reading_time_minutes }} min read</span>
                            @if ($blogPost->category_name)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $blogPost->category_name }}
                                </span>
                            @endif
                        </div>

                        <h2 class="mt-3 text-xl font-bold tracking-tight text-gray-900">
                            <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" class="transition hover:text-fuchsia-600">
                                {{ $blogPost->public_title }}
                            </a>
                        </h2>

                        @if ($blogPost->public_excerpt)
                            <p class="mt-2 text-sm leading-6 text-gray-600 line-clamp-2">
                                {{ $blogPost->public_excerpt }}
                            </p>
                        @endif

                        @if ($blogPost->tags_list !== [])
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($blogPost->tags_list as $tag)
                                    <a href="{{ route('blog.index', array_filter(['category' => $activeCategory, 'tag' => $tag])) }}"
                                       class="rounded-full bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700 transition hover:bg-fuchsia-100">
                                        {{ $tag }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" class="inline-flex items-center text-sm font-semibold text-fuchsia-600 transition hover:text-fuchsia-700">
                                Read article &rarr;
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $blogPosts->links() }}
            </div>
        @endif
    </section>

    <section class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-12 sm:px-6 lg:flex-row lg:items-center lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-fuchsia-600">Learn Faster</p>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Check slang meanings in the app too.</h2>
                <p class="mt-3 max-w-2xl text-gray-600">
                    Use the app for quick lookup, pronunciation, and example-based learning while reading these articles.
                </p>
            </div>

            @if ($playStoreUrl)
                <a
                    href="{{ $playStoreUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    data-cta-track
                    data-cta-target="google_play"
                    data-cta-source-type="blog_index"
                    data-cta-placement="footer"
                    class="inline-flex items-center rounded-full bg-fuchsia-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700"
                >
                    Download on Google Play
                </a>
            @endif
        </div>
    </section>
@endsection
