<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'kslang - Learn Korean Slang')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preload" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" as="style" crossorigin onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" crossorigin></noscript>
    @yield('head')
    @yield('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $resolvedPlayStoreUrl = isset($playStoreUrl)
        ? \App\Models\AppSetting::resolvePlayStoreUrl($playStoreUrl)
        : \App\Models\AppSetting::getPlayStoreUrl();
@endphp
<body
    data-cta-endpoint="{{ route('cta-clicks.store') }}"
    class="min-h-screen flex flex-col @yield('body_class', 'bg-white text-gray-900')"
>
    <x-public.navbar :play-store-url="$resolvedPlayStoreUrl" />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-public.footer :play-store-url="$resolvedPlayStoreUrl" />

    @stack('scripts')
</body>
</html>
