@extends('layouts.public')

@section('title', 'kslang | Korean Slang, Trending Buzzwords & Street Talk — Curated in Korea')

@php
    $schemaContextKey = '@'.'context';
    $schemaTypeKey = '@'.'type';
@endphp

@section('head')
    <link rel="canonical" href="{{ url('/') }}">
@endsection

@section('meta')
    <meta name="description" content="Learn Korean slang, trending buzzwords, and street talk — hand-reviewed by a Korean admin living in Korea. From viral expressions to swear words textbooks skip, with native audio and real-life examples. Free on Google Play.">
    <meta name="keywords" content="Korean slang, Korean trending words, Korean buzzwords, Korean slang words, Korean bad words, Korean swear words, Korean expressions, Korean internet slang, Korean curse words, how to swear in Korean, learn Korean, K-drama language, Korean street talk, funny Korean words, kslang app, Korean slang app">
    <meta name="robots" content="index, follow">

    <meta property="og:title" content="kslang — Korean Slang, Trending Buzzwords & Street Talk">
    <meta property="og:description" content="Not just curse words. Korean slang, trending buzzwords, and street talk — hand-reviewed by a Korean admin in Korea. Native audio, real-life examples, and a 4-level intensity system.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="kslang">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="kslang - Learn Korean Slang the Fun Way">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="kslang — Korean Slang, Trending Buzzwords & Street Talk">
    <meta name="twitter:description" content="Not just curse words. Korean slang, trending buzzwords, and street talk — hand-reviewed by a Korean admin in Korea. Native audio and real-life examples.">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:image:alt" content="kslang - Learn Korean Slang the Fun Way">

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode([
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'WebPage',
        'name' => 'kslang — Korean Slang, Trending Buzzwords & Street Talk',
        'description' => 'Korean slang, trending buzzwords, and street talk — hand-reviewed by a Korean admin in Korea. Native audio and real-life examples.',
        'url' => url('/'),
        'inLanguage' => 'en',
        'publisher' => [
            $schemaTypeKey => 'Organization',
            'name' => 'kslang',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}

    {!! '<script type="application/ld+json">' !!}
    {!! json_encode(array_filter([
        $schemaContextKey => 'https://schema.org',
        $schemaTypeKey => 'SoftwareApplication',
        'name' => 'kslang',
        'operatingSystem' => 'Android',
        'applicationCategory' => 'EducationalApplication',
        'description' => 'Korean slang, trending buzzwords, and street talk — curated and verified by a Korean admin in Korea. Native audio, real-life examples, and a 4-level intensity system.',
        'offers' => [
            $schemaTypeKey => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
        ],
        'installUrl' => !empty($playStoreUrl) ? $playStoreUrl : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    {!! '</script>' !!}
@endsection

@section('body_class', 'bg-slate-950 text-slate-100 selection:bg-fuchsia-500 selection:text-white')

@section('content')
    <div class="overflow-hidden">
        @include('partials.landing.hero', ['playStoreUrl' => $playStoreUrl])

        {{-- Below-fold sections: content-visibility skips layout/paint until scrolled near --}}
        <div style="content-visibility:auto;contain-intrinsic-size:auto 800px">
            @include('partials.landing.target-audience')
        </div>
        <div style="content-visibility:auto;contain-intrinsic-size:auto 900px">
            @include('partials.landing.features')
        </div>
        <div style="content-visibility:auto;contain-intrinsic-size:auto 700px">
            @include('partials.landing.curated')
        </div>
        <div style="content-visibility:auto;contain-intrinsic-size:auto 900px">
            @include('partials.landing.how-it-works')
        </div>
        <div style="content-visibility:auto;contain-intrinsic-size:auto 600px">
            @include('partials.landing.preview', ['previewSlangs' => $previewSlangs])
        </div>
        <div style="content-visibility:auto;contain-intrinsic-size:auto 500px">
            @include('partials.landing.download', ['playStoreUrl' => $playStoreUrl])
        </div>
    </div>
@endsection
