<section id="sneak-peek" class="py-24 bg-slate-950 relative border-t border-slate-900">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[30%] -left-[10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[100px]"></div>
        <div class="absolute bottom-[10%] -right-[10%] w-[40%] h-[40%] rounded-full bg-fuchsia-600/10 blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
                Korean Slang Examples 👀
            </h2>
            <p class="text-lg text-slate-400">
                A sneak peek at the Korean slang words and expressions you'll master. This is just the beginning.
            </p>
        </div>

        @if ($previewSlangs->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($previewSlangs as $slang)
                    <div class="group bg-slate-900 border border-slate-800 rounded-3xl p-6 hover:border-slate-700 transition-all duration-300 relative overflow-hidden flex flex-col h-full hover:-translate-y-1 hover:shadow-[0_10px_40px_-15px_rgba(0,0,0,0.5)]">
                        <!-- Card Glow Effect on Hover -->
                        <div class="absolute -inset-1 bg-gradient-to-r from-fuchsia-500 to-cyan-500 opacity-0 group-hover:opacity-20 blur-xl transition-opacity duration-500 rounded-3xl -z-10"></div>
                        
                        <div class="flex justify-between items-start mb-4">
                            @if ($slang->level === 1)
                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-green-500/10 text-green-400 border border-green-500/20">
                                    Mild
                                </span>
                            @elseif ($slang->level === 2)
                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                    Moderate
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-red-500/10 text-red-400 border border-red-500/20">
                                    Lvl {{ $slang->level }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-3xl font-black text-white tracking-tight">
                            {{ $slang->korean }}
                        </h3>

                        <p class="text-sm font-mono text-cyan-400 mt-2">
                            [{{ $slang->pronunciation }}]
                        </p>

                        <div class="mt-4 pt-4 border-t border-slate-800 flex-1">
                            <p class="text-slate-400 text-sm line-clamp-3 leading-relaxed">
                                {{ $slang->english_description }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center max-w-2xl mx-auto">
                <div class="text-6xl mb-4">🤫</div>
                <h3 class="text-2xl font-bold text-white mb-2">Shhh...</h3>
                <p class="text-slate-400">
                    The vault is currently closed. Download the app to be the first to unlock our slang database.
                </p>
            </div>
        @endif
    </div>
</section>
