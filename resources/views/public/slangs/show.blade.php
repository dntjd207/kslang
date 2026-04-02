@extends('layouts.public')

@section('title', $slang->resolved_seo_title . ' | kslang')

@php
    $schemaContextKey = '@'.'context';
    $schemaTypeKey = '@'.'type';
@endphp

@section('head')
    <link rel="canonical" href="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}">
@endsection

@php
    $faqItems = !empty($slang->faq_items) ? $slang->faq_items : [];

    $definedTermSchema = [
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'DefinedTerm',
        'name' => $slang->korean,
        'alternateName' => array_values(array_filter([
            $slang->pronunciation,
            $slang->public_title_en,
        ])),
        'termCode' => $slang->public_slug,
        'description' => $slang->public_summary,
        'inDefinedTermSet' => route('slangs.public.index'),
        'url' => route('slangs.public.show', ['slang' => $slang->public_slug]),
        'additionalProperty' => array_values(array_filter([
            [
                $schemaTypeKey => 'PropertyValue',
                'name' => 'Intensity level',
                'value' => $slang->level_label,
            ],
            [
                $schemaTypeKey => 'PropertyValue',
                'name' => 'Usage frequency',
                'value' => $slang->usage_frequency,
            ],
            $slang->categories->isNotEmpty() ? [
                $schemaTypeKey => 'PropertyValue',
                'name' => 'Categories',
                'value' => $slang->categories->pluck('name')->implode(', '),
            ] : null,
        ])),
    ];

    if ($slang->audio_url) {
        $definedTermSchema['associatedMedia'] = [[
            $schemaTypeKey => 'AudioObject',
            'name' => "{$slang->korean} pronunciation audio",
            'contentUrl' => $slang->audio_url,
            'encodingFormat' => 'audio/mpeg',
        ]];
    }
@endphp

@section('meta')
    <meta name="description" content="{{ $slang->resolved_seo_description }}">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ $slang->resolved_seo_title }}">
    <meta property="og:description" content="{{ $slang->resolved_seo_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $slang->resolved_seo_title }}">
    <meta name="twitter:description" content="{{ $slang->resolved_seo_description }}">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode($definedTermSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
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
                'name' => 'Korean Slang',
                'item' => route('slangs.public.index'),
            ],
            [
                $schemaTypeKey => 'ListItem',
                'position' => 3,
                'name' => $slang->korean,
                'item' => route('slangs.public.show', ['slang' => $slang->public_slug]),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}

    @if (!empty($faqItems))
        {!! '<script type="application/ld+json">' !!}
        {!! json_encode([
            $schemaContextKey => 'https://schema.org',
            $schemaTypeKey => 'FAQPage',
            'mainEntity' => collect($faqItems)->map(function (array $item) use ($schemaTypeKey): array {
                return [
                    $schemaTypeKey => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        $schemaTypeKey => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            })->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        {!! '</script>' !!}
    @endif
@endsection

@section('content')
    <section class="border-b border-gray-200 bg-gradient-to-b from-cyan-50 via-white to-white">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('landing') }}" class="transition hover:text-cyan-600">Home</a>
                <span class="mx-2 text-gray-300">/</span>
                <a href="{{ route('slangs.public.index') }}" class="transition hover:text-cyan-600">Korean Slang</a>
            </nav>

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                            {{ $slang->level_label }}
                        </span>
                        @if ($slang->is_new)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                New
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-5 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        {{ $slang->public_title }}
                    </h1>

                    <p class="mt-3 text-lg text-gray-500">
                        {{ $slang->korean }} · {{ $slang->pronunciation }}
                    </p>

                    @if ($slang->audio_url)
                        <div class="mt-5 flex items-center gap-4 rounded-2xl border border-cyan-200 bg-cyan-50/60 px-5 py-4">
                            <div class="shrink-0">
                                <p class="text-sm font-semibold text-cyan-900">Listen to pronunciation</p>
                            </div>
                            <audio class="h-10 w-full min-w-0" controls preload="none" src="{{ $slang->audio_url }}"></audio>
                        </div>
                    @endif

                    <p class="mt-5 max-w-3xl text-lg leading-8 text-gray-600">
                        {{ $slang->public_summary }}
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Pronunciation</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $slang->pronunciation ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Intensity</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $slang->level_label }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Frequency</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $slang->usage_frequency }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Categories</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">
                                {{ $slang->categories->isNotEmpty() ? $slang->categories->pluck('name')->implode(', ') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-cyan-200 bg-cyan-50 p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-700">App</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900">Study this slang inside kslang.</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Listen to pronunciation, review examples, and keep related slang together while learning.
                    </p>

                    @if ($playStoreUrl)
                        <a
                            href="{{ $playStoreUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-cta-track
                            data-cta-target="google_play"
                            data-cta-source-type="slang_show"
                            data-cta-placement="hero"
                            data-cta-slang-id="{{ $slang->id }}"
                            class="mt-5 inline-flex items-center rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                        >
                            Download on Google Play
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-8">
                <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">Meaning</h2>
                    <p class="mt-4 text-base leading-8 text-gray-700">{{ $slang->english_description }}</p>

                    @if ($slang->korean_description)
                        <div class="mt-6 rounded-2xl bg-gray-50 p-5">
                            <p class="text-sm font-semibold text-gray-900">Korean editor note</p>
                            <p class="mt-2 text-sm leading-7 text-gray-600">{{ $slang->korean_description }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">Nuance and usage context</h2>
                    <p class="mt-4 text-base leading-8 text-gray-700">{{ $slang->english_usage_context }}</p>
                    <div class="mt-6 rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm font-semibold text-gray-900">Korean editor note</p>
                        <p class="mt-2 text-sm leading-7 text-gray-600">{{ $slang->usage_context }}</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">Examples</h2>

                    @if ($slang->examples->isEmpty())
                        <p class="mt-4 text-gray-500">No example sentences have been published yet.</p>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($slang->examples as $example)
                                <div class="rounded-2xl border border-gray-200 p-5">
                                    <p class="font-semibold text-gray-900">{{ $example->korean_example }}</p>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $example->english_example }}</p>
                                    @if ($example->audio_url)
                                        <audio class="mt-3 h-9 w-full" controls preload="none" src="{{ $example->audio_url }}"></audio>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if (!empty($faqItems))
                    <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900">FAQ</h2>
                        <div class="mt-6 space-y-4">
                            @foreach ($faqItems as $faqItem)
                                <div class="rounded-2xl border border-gray-200 p-5">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $faqItem['question'] }}</h3>
                                    <p class="mt-2 text-sm leading-7 text-gray-600">{{ $faqItem['answer'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900">Related blog articles</h2>

                    @if ($slang->blogPosts->isEmpty())
                        <p class="mt-3 text-sm leading-6 text-gray-500">No related articles are linked yet.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($slang->blogPosts as $blogPost)
                                <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" class="block rounded-2xl border border-gray-200 px-4 py-3 transition hover:border-cyan-200 hover:bg-cyan-50/50">
                                    <p class="font-semibold text-gray-900">{{ $blogPost->public_title }}</p>
                                    @if ($blogPost->public_excerpt)
                                        <p class="mt-1 text-sm leading-6 text-gray-500">{{ $blogPost->public_excerpt }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900">Related slang pages</h2>

                    @if ($relatedSlangs->isEmpty())
                        <p class="mt-3 text-sm leading-6 text-gray-500">No related slang pages are available yet.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($relatedSlangs as $relatedSlang)
                                <a href="{{ route('slangs.public.show', ['slang' => $relatedSlang->public_slug]) }}" class="block rounded-2xl border border-gray-200 px-4 py-3 transition hover:border-cyan-200 hover:bg-cyan-50/50">
                                    <p class="font-semibold text-gray-900">{{ $relatedSlang->korean }}</p>
                                    <p class="mt-1 text-sm leading-6 text-gray-500">{{ $relatedSlang->public_summary }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-700">App</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900">Study this slang inside kslang.</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Listen to pronunciation, review examples, and keep related slang together while learning.
                    </p>

                    @if ($playStoreUrl)
                        <a
                            href="{{ $playStoreUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-cta-track
                            data-cta-target="google_play"
                            data-cta-source-type="slang_show"
                            data-cta-placement="sidebar"
                            data-cta-slang-id="{{ $slang->id }}"
                            class="mt-5 inline-flex items-center rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                        >
                            Download on Google Play
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </section>
@endsection
