@props([
    'playStoreUrl' => '',
])

@php
    $isLanding = request()->routeIs('landing');
    $navBg = $isLanding ? 'bg-transparent absolute top-0 w-full z-50 border-b border-white/10' : 'bg-white shadow-sm border-b border-gray-200';
    $textColor = $isLanding ? 'text-slate-300' : 'text-gray-700';
    $logoColor = $isLanding ? 'text-white' : 'text-fuchsia-600';
    $hoverColor = $isLanding ? 'hover:text-white' : 'hover:text-fuchsia-600';
    $activeColor = $isLanding ? 'text-fuchsia-400' : 'text-fuchsia-600';
    $mobileMenuBg = $isLanding ? 'bg-slate-900/95 backdrop-blur border-b border-white/10' : 'bg-white border-b border-gray-200';
    $mobileTextColor = $isLanding ? 'text-slate-200' : 'text-gray-700';
    $mobileHoverBg = $isLanding ? 'hover:bg-white/10' : 'hover:bg-gray-50';
    $mobileActiveColor = $isLanding ? 'text-fuchsia-400 bg-white/10' : 'text-fuchsia-600 bg-fuchsia-50';
    $hamburgerColor = $isLanding ? 'text-white' : 'text-gray-700';
    $ctaClasses = $isLanding
        ? 'inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur transition hover:bg-white/20 sm:px-4 sm:text-sm'
        : 'inline-flex items-center rounded-full bg-fuchsia-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-fuchsia-700 sm:px-4 sm:text-sm';

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

            <div class="flex items-center gap-3 sm:gap-4 md:gap-6">
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

                @if ($playStoreUrl)
                    <a
                        href="{{ $playStoreUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Download the kslang app"
                        data-cta-track
                        data-cta-target="google_play"
                        data-cta-source-type="site_nav"
                        data-cta-placement="navbar"
                        class="{{ $ctaClasses }}"
                    >
                        <span class="hidden sm:inline">Download App</span>
                        <span class="sm:hidden">App</span>
                    </a>
                @endif

                <button
                    id="mobile-menu-btn"
                    type="button"
                    aria-label="Toggle navigation menu"
                    aria-expanded="false"
                    class="md:hidden inline-flex items-center justify-center rounded-lg p-2 transition {{ $hamburgerColor }} hover:bg-black/10"
                >
                    <svg id="mobile-menu-icon-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg id="mobile-menu-icon-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden {{ $mobileMenuBg }}">
        <div class="space-y-1 px-4 pb-4 pt-2">
            @foreach ($links as $link)
                @php
                    $isActive = request()->routeIs($link['pattern']);
                @endphp
                <a href="{{ route($link['route']) }}"
                   class="block rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive ? $mobileActiveColor : $mobileTextColor . ' ' . $mobileHoverBg }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('mobile-menu-icon-open');
        const iconClose = document.getElementById('mobile-menu-icon-close');
        const isHidden = menu.classList.toggle('hidden');
        iconOpen.classList.toggle('hidden', !isHidden);
        iconClose.classList.toggle('hidden', isHidden);
        this.setAttribute('aria-expanded', !isHidden);
    });
</script>
