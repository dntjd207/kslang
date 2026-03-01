@extends('layouts.public')

@section('title', 'kslang | Learn Korean Slang, Swear Words & Street Talk')

@section('canonical', url('/'))

@section('meta')
    <meta name="description" content="Learn real Korean slang, bad words, and curse words with native audio and real-life examples. A 4-level system from mild to extreme. Download free on Google Play.">
    <meta name="keywords" content="Korean slang, Korean slang words, Korean bad words, Korean swear words, Korean curse words, Korean insults, Korean cuss words, how to swear in Korean, learn Korean, K-drama language, Korean street talk, Korean expressions, funny Korean words, kslang app">
    <meta name="robots" content="index, follow">

    <meta property="og:title" content="kslang — Learn Korean Slang, Bad Words & Street Talk">
    <meta property="og:description" content="Learn real Korean slang and swear words with native audio, real-life examples, and a 4-level intensity system. Download free.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="kslang">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="kslang - Learn Korean Slang the Fun Way">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="kslang — Learn Korean Slang, Bad Words & Street Talk">
    <meta name="twitter:description" content="Learn real Korean slang and swear words with native audio, real-life examples, and a 4-level intensity system.">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:image:alt" content="kslang - Learn Korean Slang the Fun Way">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "kslang — Learn Korean Slang, Bad Words & Street Talk",
        "description": "Learn real Korean slang, bad words, and curse words with native audio and real-life examples.",
        "url": "{{ url('/') }}",
        "inLanguage": "en",
        "publisher": {
            "@type": "Organization",
            "name": "kslang"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "kslang",
        "operatingSystem": "Android",
        "applicationCategory": "EducationalApplication",
        "description": "Learn real Korean slang, swear words, and street talk with native audio pronunciation, real-life examples, and a 4-level intensity system.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
        @if (!empty($playStoreUrl))
        ,"installUrl": "{{ $playStoreUrl }}"
        @endif
    }
    </script>
@endsection

@section('body_class', 'bg-slate-950 text-slate-100 selection:bg-fuchsia-500 selection:text-white')

@section('content')
    <div class="overflow-hidden">
        @include('partials.landing.hero', ['playStoreUrl' => $playStoreUrl])
        @include('partials.landing.target-audience')
        @include('partials.landing.features')
        @include('partials.landing.how-it-works')
        @include('partials.landing.preview', ['previewSlangs' => $previewSlangs])
        @include('partials.landing.download', ['playStoreUrl' => $playStoreUrl])
    </div>
@endsection
