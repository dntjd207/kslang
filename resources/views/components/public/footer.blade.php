@php
    $isLanding = request()->routeIs('landing');
    $footerBg = $isLanding ? 'bg-slate-950' : 'bg-gray-900 text-gray-400';
    $textColor = $isLanding ? 'text-slate-500' : 'text-gray-400';
@endphp

<footer class="{{ $footerBg }} relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row justify-center items-center">
            <div class="{{ $textColor }} text-sm font-medium flex items-center gap-2">
                <span class="text-lg font-black {{ $isLanding ? 'text-slate-700' : 'text-gray-600' }}">kslang</span>
                <span>&copy; {{ date('Y') }}. All rights reserved.</span>
            </div>
        </div>
    </div>
</footer>
