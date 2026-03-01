<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'kslang - Learn Korean Slang')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @yield('head')
    @yield('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col @yield('body_class', 'bg-white text-gray-900')">
    <x-public.navbar />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-public.footer />

    @stack('scripts')
</body>
</html>
