@extends('layouts.public')

@section('title', 'Korean Slang Dictionary | kslang')

@section('head')
    <link rel="canonical" href="{{ route('slangs.public.index') }}">
@endsection

@section('meta')
    <meta name="description" content="Browse public Korean slang pages with English explanations, usage context, examples, and related blog articles.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Korean Slang Dictionary | kslang">
    <meta property="og:description" content="Browse public Korean slang pages with English explanations and related blog articles.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('slangs.public.index') }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Korean Slang Dictionary | kslang">
    <meta name="twitter:description" content="Browse public Korean slang pages with English explanations and related blog articles.">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">
@endsection

@section('content')
    <section class="border-b border-gray-200 bg-gradient-to-b from-cyan-50 via-white to-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-600">Dictionary</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                Public Korean slang pages for English learners.
            </h1>
            <p class="mt-4 max-w-3xl text-lg leading-8 text-gray-600">
                Browse slang explanations, nuance, category context, and related articles in one searchable hub.
            </p>

            @if ($playStoreUrl)
                <div class="mt-6">
                    <a
                        href="{{ $playStoreUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        data-cta-track
                        data-cta-target="google_play"
                        data-cta-source-type="slang_index"
                        data-cta-placement="hero"
                        class="inline-flex items-center rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                    >
                        Download on Google Play
                    </a>
                </div>
            @endif

            <form method="GET" action="{{ route('slangs.public.index') }}" class="mt-8 max-w-2xl">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search by Korean, pronunciation, or meaning"
                        class="h-12 flex-1 rounded-full border border-gray-300 px-5 text-sm shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                    <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-cyan-600 px-6 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($slangs->isEmpty())
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900">No slang pages found</h2>
                <p class="mt-3 text-gray-600">Try another keyword or check back after new public entries are published.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($slangs as $slang)
                    <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $slang->korean }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ $slang->pronunciation }}</p>
                            </div>

                            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                                {{ $slang->level_label }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-gray-600">
                            {{ $slang->public_summary }}
                        </p>

                        @if ($slang->categories->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($slang->categories as $category)
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6">
                            <a href="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}" class="inline-flex items-center text-sm font-semibold text-cyan-600 transition hover:text-cyan-700">
                                View details
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $slangs->links() }}
            </div>
        @endif
    </section>

    <section class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-12 sm:px-6 lg:flex-row lg:items-center lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-600">App</p>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Keep Korean slang handy on mobile.</h2>
                <p class="mt-3 max-w-2xl text-gray-600">
                    Use the app to browse slang faster, listen to pronunciation, and learn with examples.
                </p>
            </div>

            @if ($playStoreUrl)
                <a
                    href="{{ $playStoreUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    data-cta-track
                    data-cta-target="google_play"
                    data-cta-source-type="slang_index"
                    data-cta-placement="footer"
                    class="inline-flex items-center rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                >
                    Download on Google Play
                </a>
            @endif
        </div>
    </section>
@endsection
