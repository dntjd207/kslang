<section class="relative py-24 md:py-32 overflow-hidden border-t border-slate-900">
    <!-- Gradient Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-900 via-slate-950 to-blue-900"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')]"></div>

    <!-- Glowing Orbs -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-fuchsia-500/20 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500/20 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/3"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl md:text-6xl font-black text-white mb-6 tracking-tight">
            Ready to Talk Like <br class="hidden sm:block"/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 to-cyan-400">a Real Korean?</span>
        </h2>
        
        <p class="text-xl text-slate-300 mb-10 max-w-2xl mx-auto">
            Trending buzzwords, slang, and street talk — all verified by a real Korean in Seoul. Download kslang and stop being the only one who doesn't get the joke.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if (!empty($playStoreUrl))
                <a href="{{ $playStoreUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-200 bg-white/10 border border-white/20 rounded-full backdrop-blur-md hover:bg-white/20 hover:scale-105 hover:border-fuchsia-500/50 hover:shadow-[0_0_30px_-5px_rgba(217,70,239,0.5)]">
                    <svg class="w-8 h-8 mr-3 text-fuchsia-400 group-hover:text-fuchsia-300 transition-colors" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-[10px] uppercase tracking-wider text-slate-300">Get it on</div>
                        <div class="text-lg leading-none -mt-0.5">Google Play</div>
                    </div>
                </a>
            @else
                <span class="inline-flex items-center justify-center px-8 py-4 font-bold text-slate-400 bg-white/5 border border-white/10 rounded-full backdrop-blur-md cursor-not-allowed">
                    Dropping Soon 🔥
                </span>
            @endif
        </div>
    </div>
</section>
