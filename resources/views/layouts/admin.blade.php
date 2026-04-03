<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'kslang Admin')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preload" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" as="style" crossorigin onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" crossorigin></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-100" style="font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif">
    <div class="flex h-screen overflow-hidden">
        <x-admin.sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <x-admin.header :title="$pageTitle ?? ''" />

            <main class="flex-1 overflow-y-auto p-6">
                <x-admin.alert />

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
