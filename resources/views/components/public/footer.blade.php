@php
    $isLanding = request()->routeIs('landing');
    $footerBg = $isLanding ? 'bg-slate-950' : 'bg-gray-900 text-gray-400';
    $textColor = $isLanding ? 'text-slate-500' : 'text-gray-400';
@endphp

<footer class="{{ $footerBg }} relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col items-center gap-4">
            <div class="{{ $textColor }} text-sm font-medium flex items-center gap-2">
                <span class="text-lg font-black {{ $isLanding ? 'text-slate-700' : 'text-gray-600' }}">kslang</span>
                <span>&copy; {{ date('Y') }}. All rights reserved.</span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4 text-sm">
                <a href="{{ route('blog.index') }}" class="{{ $isLanding ? 'text-slate-400 hover:text-white' : 'text-gray-400 hover:text-white' }} transition-colors">
                    Blog
                </a>
                <a href="{{ route('slangs.public.index') }}" class="{{ $isLanding ? 'text-slate-400 hover:text-white' : 'text-gray-400 hover:text-white' }} transition-colors">
                    Korean Slang
                </a>
                <a href="{{ route('privacy') }}" class="{{ $isLanding ? 'text-slate-400 hover:text-white' : 'text-gray-400 hover:text-white' }} transition-colors">
                    Privacy
                </a>
                <a href="{{ route('terms') }}" class="{{ $isLanding ? 'text-slate-400 hover:text-white' : 'text-gray-400 hover:text-white' }} transition-colors">
                    Terms
                </a>
            </div>
        </div>
    </div>
</footer>
