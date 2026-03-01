@php
    $isLanding = request()->routeIs('landing');
    $navBg = $isLanding ? 'bg-transparent absolute top-0 w-full z-50 border-b border-white/10' : 'bg-white shadow-sm border-b border-gray-200';
    $textColor = $isLanding ? 'text-slate-300' : 'text-gray-700';
    $logoColor = $isLanding ? 'text-white' : 'text-fuchsia-600';
    $hoverColor = $isLanding ? 'hover:text-white' : 'hover:text-fuchsia-600';
    $activeColor = $isLanding ? 'text-fuchsia-400' : 'text-fuchsia-600';
    $mobileMenuBg = $isLanding ? 'bg-slate-900 border-b border-white/10' : 'bg-white border-b border-gray-200';
@endphp

<nav class="{{ $navBg }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ route('landing') }}" class="text-2xl font-black tracking-tight {{ $logoColor }}">
                kslang<span class="text-fuchsia-500">.</span>
            </a>

            <div class="hidden md:flex space-x-8">
                <a href="{{ route('landing') }}"
                   class="{{ request()->routeIs('landing') ? $activeColor . ' font-bold' : $textColor . ' ' . $hoverColor }} transition font-medium">Home</a>
            </div>

            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md {{ $textColor }} hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2 {{ $mobileMenuBg }} absolute w-full left-0 px-4 pt-2 shadow-xl z-50">
            <a href="{{ route('landing') }}" class="block px-3 py-2 rounded-md {{ $textColor }} {{ $hoverColor }} font-medium">Home</a>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>
