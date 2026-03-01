<section class="relative pt-32 pb-20 md:pt-48 md:pb-32 flex flex-col items-center justify-center min-h-[90vh]">
    <!-- Background Effects -->
    <div class="absolute inset-0 overflow-hidden -z-10">
        <div class="absolute -top-[40%] -left-[20%] w-[70%] h-[70%] rounded-full bg-fuchsia-600/20 blur-[120px]"></div>
        <div class="absolute top-[20%] -right-[20%] w-[60%] h-[60%] rounded-full bg-cyan-600/20 blur-[120px]"></div>
        <div class="absolute -bottom-[20%] left-[20%] w-[80%] h-[80%] rounded-full bg-blue-600/20 blur-[120px]"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-800 backdrop-blur-md mb-8">
            <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-sm font-medium text-slate-300">The #1 app for Korean slang words & street talk</span>
        </div>

        <h1 class="text-5xl sm:text-6xl md:text-8xl font-extrabold tracking-tight mb-6">
            <span class="block text-slate-100">Learn Korean Slang</span>
            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-500 via-purple-500 to-cyan-500 pb-2">Like a Native.</span>
        </h1>

        <p class="text-lg md:text-2xl text-slate-400 max-w-3xl mx-auto mb-12 leading-relaxed">
            Stop sounding like a textbook. Discover <strong class="text-slate-200">Korean bad words, curse words, and slang expressions</strong> that K-drama characters and real Koreans actually use — with native audio and real-life context.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
            @if (!empty($playStoreUrl))
                <a href="{{ $playStoreUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Download kslang on Google Play"
                   class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-200 bg-fuchsia-600 rounded-full hover:bg-fuchsia-500 hover:scale-105 hover:shadow-[0_0_40px_-10px_rgba(217,70,239,0.7)] focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                    <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    Download on Google Play
                </a>
            @else
                <button disabled
                   class="inline-flex items-center justify-center px-8 py-4 font-bold text-slate-400 bg-slate-800 rounded-full cursor-not-allowed border border-slate-700">
                    App Coming Soon 🚀
                </button>
            @endif

            <a href="#sneak-peek" class="inline-flex items-center justify-center px-8 py-4 font-bold text-slate-300 transition-all duration-200 bg-transparent border border-slate-700 rounded-full hover:bg-slate-800 hover:text-white">
                See a Sneak Peek
            </a>
        </div>

        <div class="mt-16 pt-8 border-t border-slate-800/50 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 max-w-4xl mx-auto">
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-white">4</p>
                <p class="text-sm text-slate-400 mt-1 uppercase tracking-wider font-semibold">Intensity Levels</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-white">100%</p>
                <p class="text-sm text-slate-400 mt-1 uppercase tracking-wider font-semibold">Native Audio</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-white">Real</p>
                <p class="text-sm text-slate-400 mt-1 uppercase tracking-wider font-semibold">Life Contexts</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-black text-white">Free</p>
                <p class="text-sm text-slate-400 mt-1 uppercase tracking-wider font-semibold">To Start</p>
            </div>
        </div>
    </div>
</section>
