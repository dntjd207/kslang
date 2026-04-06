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
        'datePublished' => $slang->created_at->toIso8601String(),
        'dateModified' => $slang->updated_at->toIso8601String(),
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
        $definedTermSchema['additionalProperty'][] = [
            $schemaTypeKey => 'PropertyValue',
            'name' => 'Audio available',
            'value' => 'In app only',
        ];
    }
@endphp

@section('meta')
    <meta name="description" content="{{ $slang->resolved_seo_description }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    @if ($slang->seo_keywords_en)
        <meta name="keywords" content="{{ $slang->seo_keywords_en }}">
    @endif
    <meta property="og:site_name" content="kslang">
    <meta property="og:locale" content="en_US">
    <meta property="og:title" content="{{ $slang->resolved_seo_title }}">
    <meta property="og:description" content="{{ $slang->resolved_seo_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $slang->korean }} - Korean Slang Meaning">
    <meta property="article:published_time" content="{{ $slang->created_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $slang->updated_at->toIso8601String() }}">
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

                    <div class="mt-5 flex items-center gap-4 rounded-2xl border border-cyan-200 bg-cyan-50/60 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <svg class="size-5 shrink-0 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM18.584 5.106a.75.75 0 0 1 1.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 0 1-1.06-1.06 8.25 8.25 0 0 0 0-11.668.75.75 0 0 1 0-1.06Z" />
                                <path d="M15.932 7.757a.75.75 0 0 1 1.061 0 6 6 0 0 1 0 8.486.75.75 0 0 1-1.06-1.061 4.5 4.5 0 0 0 0-6.364.75.75 0 0 1 0-1.06Z" />
                            </svg>
                            <p class="text-sm font-semibold text-cyan-900">Listen to pronunciation</p>
                        </div>
                        @if ($playStoreUrl)
                            <a
                                href="{{ $playStoreUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                data-cta-track
                                data-cta-target="google_play"
                                data-cta-source-type="slang_show"
                                data-cta-placement="audio"
                                data-cta-slang-id="{{ $slang->id }}"
                                class="ml-auto inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-cyan-700"
                            >
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" /></svg>
                                Listen in App
                            </a>
                        @else
                            <span class="ml-auto inline-flex items-center gap-2 rounded-full bg-gray-400 px-4 py-2 text-xs font-semibold text-white">
                                Listen in App
                            </span>
                        @endif
                    </div>

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
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-700">App only</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900">Get the full picture in kslang.</h2>
                    <ul class="mt-4 space-y-2.5 text-sm leading-6 text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            Listen to native pronunciation
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            All examples with audio
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            Full nuance &amp; usage context
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            Daily slang &amp; quizzes
                        </li>
                    </ul>

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

                <div class="rounded-3xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">Nuance and usage context</h2>
                    <p class="mt-4 text-base leading-7 text-gray-600">
                        Understanding when and how to use <span class="font-semibold text-gray-900">{{ $slang->korean }}</span> matters as much as knowing its meaning. Tone, social setting, and formality all affect how this word lands.
                    </p>
                    @if ($playStoreUrl)
                        <a
                            href="{{ $playStoreUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-cta-track
                            data-cta-target="google_play"
                            data-cta-source-type="slang_show"
                            data-cta-placement="usage_context"
                            data-cta-slang-id="{{ $slang->id }}"
                            class="mt-5 inline-flex items-center gap-2 rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" /></svg>
                            Read full context in App
                        </a>
                    @endif
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">Examples</h2>

                    @if ($slang->examples->isEmpty())
                        <p class="mt-4 text-gray-500">No example sentences have been published yet.</p>
                    @else
                        @php($firstExample = $slang->examples->first())
                        @php($remainingCount = $slang->examples->count() - 1)

                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl border border-gray-200 p-5">
                                <p class="font-semibold text-gray-900">{{ $firstExample->korean_example }}</p>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $firstExample->english_example }}</p>
                            </div>

                            @if ($remainingCount > 0)
                                <div class="rounded-2xl border border-dashed border-cyan-300 bg-cyan-50/50 p-5 text-center">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $remainingCount }} more example{{ $remainingCount > 1 ? 's' : '' }} with audio available in the app
                                    </p>
                                    @if ($playStoreUrl)
                                        <a
                                            href="{{ $playStoreUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            data-cta-track
                                            data-cta-target="google_play"
                                            data-cta-source-type="slang_show"
                                            data-cta-placement="examples"
                                            data-cta-slang-id="{{ $slang->id }}"
                                            class="mt-3 inline-flex items-center gap-2 rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700"
                                        >
                                            See all examples in App
                                        </a>
                                    @endif
                                </div>
                            @endif
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

                <div class="rounded-3xl border border-cyan-200 bg-cyan-50 p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-700">App only</p>
                    <h2 class="mt-3 text-xl font-bold text-gray-900">Unlock the full learning experience</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Audio, all examples, usage context, and more — only in the kslang app.
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
