@php
    $isLanding = request()->routeIs('landing');
    $navBg = $isLanding ? 'bg-transparent absolute top-0 w-full z-50 border-b border-white/10' : 'bg-white shadow-sm border-b border-gray-200';
    $textColor = $isLanding ? 'text-slate-300' : 'text-gray-700';
    $logoColor = $isLanding ? 'text-white' : 'text-fuchsia-600';
    $hoverColor = $isLanding ? 'hover:text-white' : 'hover:text-fuchsia-600';
    $activeColor = $isLanding ? 'text-fuchsia-400' : 'text-fuchsia-600';
    $mobileMenuBg = $isLanding ? 'bg-slate-900 border-b border-white/10' : 'bg-white border-b border-gray-200';

    $links = [
        ['route' => 'landing', 'pattern' => 'landing', 'label' => 'Home'],
        ['route' => 'blog.index', 'pattern' => 'blog.*', 'label' => 'Blog'],
        ['route' => 'slangs.public.index', 'pattern' => 'slangs.public.*', 'label' => 'Korean Slang'],
    ];
@endphp

<nav class="{{ $navBg }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ route('landing') }}" class="text-2xl font-black tracking-tight {{ $logoColor }}">
                kslang<span class="text-fuchsia-500">.</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                @foreach ($links as $link)
                    @php
                        $isActive = request()->routeIs($link['pattern']);
                    @endphp
                    <a href="{{ route($link['route']) }}"
                       class="text-sm font-medium transition-colors {{ $isActive ? $activeColor : $textColor . ' ' . $hoverColor }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>
