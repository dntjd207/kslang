<section class="relative pt-32 pb-20 md:pt-48 md:pb-32 flex flex-col items-center justify-center min-h-[90vh]">
    <!-- Background Effects (isolated from text layer) -->
    <div class="absolute inset-0 overflow-hidden -z-10" style="contain:strict">
        <!-- Floating Blobs — will-change promotes to compositor layer, reducing main-thread paint cost -->
        <div class="absolute -top-[30%] -left-[15%] w-[65%] h-[65%] rounded-full bg-fuchsia-500/30 blur-[100px] animate-blob-1 will-change-transform"></div>
        <div class="absolute top-[10%] -right-[15%] w-[55%] h-[55%] rounded-full bg-cyan-500/25 blur-[100px] animate-blob-2 will-change-transform"></div>
        <div class="absolute -bottom-[15%] left-[10%] w-[70%] h-[70%] rounded-full bg-violet-600/25 blur-[100px] animate-blob-3 will-change-transform"></div>
        <div class="absolute top-[30%] left-[40%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 blur-[80px] animate-blob-4 will-change-transform"></div>

        <!-- Aurora Gradient Sweep -->
        <div class="absolute inset-0 bg-[linear-gradient(125deg,transparent_15%,rgba(217,70,239,0.15)_30%,rgba(139,92,246,0.12)_42%,rgba(99,102,241,0.1)_55%,rgba(6,182,212,0.15)_68%,transparent_85%)] bg-[length:250%_100%] animate-aurora will-change-[background-position]"></div>

        <!-- Center Glow (behind title) -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] md:w-[900px] md:h-[500px] rounded-full bg-[radial-gradient(ellipse,rgba(139,92,246,0.25)_0%,rgba(217,70,239,0.1)_40%,transparent_70%)] animate-glow-pulse will-change-[transform,opacity]"></div>

        <!-- Dot Grid -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white_50%,transparent)]"></div>

        <!-- Bottom fade to page bg -->
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-slate-950 to-transparent"></div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <!-- Badge with animated border -->
        <div class="hero-badge inline-flex items-center gap-2 px-4 py-2 rounded-full mb-10">
            <span class="flex h-2 w-2 rounded-full bg-green-400 animate-pulse shadow-[0_0_8px_rgba(74,222,128,0.6)]"></span>
            <span class="text-sm font-medium text-slate-200">Korean slang, trending buzzwords & street talk — curated in Seoul</span>
        </div>

        <!-- Main Heading -->
        <h1 class="text-5xl sm:text-7xl md:text-9xl font-extrabold tracking-tight mb-8">
            <span class="block text-white">Real Korean</span>
            <span class="block text-transparent bg-clip-text bg-[linear-gradient(90deg,#d946ef,#8b5cf6,#06b6d4,#d946ef,#8b5cf6)] bg-[length:200%_auto] animate-shimmer pb-2">They Don't Teach.</span>
        </h1>

        <!-- Description -->
        <p class="text-lg md:text-2xl text-slate-400 max-w-3xl mx-auto mb-14 leading-relaxed">
            Not just curse words. From <strong class="text-white/90">today's viral buzzwords and trending slang</strong> to the swear words textbooks skip — every entry is <strong class="text-white/90">hand-reviewed by a Korean admin living in Korea</strong>, so you learn what's actually being said right now.
        </p>

        <!-- CTA -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
            @if (!empty($playStoreUrl))
                <a href="{{ $playStoreUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Download kslang on Google Play"
                   data-cta-track
                   data-cta-target="google_play"
                   data-cta-source-type="landing"
                   data-cta-placement="hero"
                   class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-300 bg-gradient-to-r from-fuchsia-600 to-violet-600 rounded-full hover:from-fuchsia-500 hover:to-violet-500 hover:scale-105 hover:shadow-[0_0_50px_-8px_rgba(217,70,239,0.8)] focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                    <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    Download on Google Play
                </a>
            @else
                <button disabled
                   class="inline-flex items-center justify-center px-8 py-4 font-bold text-slate-300 bg-slate-800/80 rounded-full cursor-not-allowed border border-slate-700/50 backdrop-blur-sm shadow-lg">
                    App Coming Soon 🚀
                </button>
            @endif
        </div>

        <!-- Stats -->
        <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5 max-w-4xl mx-auto">
            <div class="group rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm p-5 md:p-6 text-center transition-all duration-300 hover:bg-white/[0.08] hover:border-white/[0.15] hover:-translate-y-1">
                <p class="text-3xl md:text-4xl font-black text-white">4</p>
                <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest font-semibold">Intensity Levels</p>
            </div>
            <div class="group rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm p-5 md:p-6 text-center transition-all duration-300 hover:bg-white/[0.08] hover:border-white/[0.15] hover:-translate-y-1">
                <p class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 to-cyan-400">Seoul</p>
                <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest font-semibold">Based Curation</p>
            </div>
            <div class="group rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm p-5 md:p-6 text-center transition-all duration-300 hover:bg-white/[0.08] hover:border-white/[0.15] hover:-translate-y-1">
                <p class="text-3xl md:text-4xl font-black text-white">100%</p>
                <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest font-semibold">Human Verified</p>
            </div>
            <div class="group rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm p-5 md:p-6 text-center transition-all duration-300 hover:bg-white/[0.08] hover:border-white/[0.15] hover:-translate-y-1">
                <p class="text-3xl md:text-4xl font-black text-white">Free</p>
                <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest font-semibold">To Start</p>
            </div>
        </div>
    </div>
</section>
