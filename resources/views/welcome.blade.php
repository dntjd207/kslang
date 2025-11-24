<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>kslang - Real Korean. Raw & Uncensored.</title>

    <!-- Fonts: Inter for English (Bold, Industrial feel) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        dark: {
                            bg: '#121212',
                            card: '#1E1E1E',
                        },
                        neon: {
                            pink: '#FF0099',
                            green: '#00FF41',
                            blue: '#00F0FF',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .neon-shadow {
            text-shadow: 0 0 10px rgba(255, 0, 153, 0.5);
        }
        .gradient-text {
            background: linear-gradient(90deg, #FF0099 0%, #00F0FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="antialiased selection:bg-neon-pink selection:text-white">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-[#121212]/90 backdrop-blur-sm border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex-shrink-0">
                    <span class="text-2xl font-black tracking-tighter text-white">kslang<span class="text-neon-pink">.</span></span>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center space-x-8">
                        <a href="#levels" class="text-sm font-bold text-gray-400 hover:text-white transition">LEVELS</a>
                        <a href="#features" class="text-sm font-bold text-gray-400 hover:text-white transition">FEATURES</a>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/home') }}" class="text-sm font-bold text-white">DASHBOARD</a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-6xl md:text-8xl font-black tracking-tighter leading-none mb-6">
                Real Korean. <br>
                <span class="gradient-text">Raw & Uncensored.</span>
            </h1>
            <p class="mt-4 text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12 font-light">
                Understanding K-Drama villains starts here. Master the slang, profanity, and cultural context your teacher is too afraid to teach you.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#levels" class="px-10 py-4 text-lg font-bold text-white border border-gray-700 rounded-full hover:border-white hover:bg-white/5 transition duration-300">
                    How it works
                </a>
            </div>
        </div>
        
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-neon-pink/20 rounded-full blur-[120px] -z-10"></div>
    </div>

    <!-- Levels System -->
    <div id="levels" class="py-24 bg-dark-card border-y border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">Choose Your Battle</h2>
                <p class="text-gray-400 text-lg">From mild exclamations to critical damage. We categorize every word.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Level 1 -->
                <div class="p-8 rounded-2xl bg-[#121212] border border-gray-800 hover:border-neon-blue transition duration-300 group">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">🍦</div>
                    <h3 class="text-xl font-bold text-neon-blue mb-2">Level 1: Mild</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Cute slang and light exclamations. Safe to use with close friends. <br><span class="text-gray-700 italic">e.g. "Heol", "Daebak"</span></p>
                </div>
                <!-- Level 2 -->
                <div class="p-8 rounded-2xl bg-[#121212] border border-gray-800 hover:border-yellow-400 transition duration-300 group">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">🌶️</div>
                    <h3 class="text-xl font-bold text-yellow-400 mb-2">Level 2: Annoying</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Rough expressions between guys. Don't use this with your boss.</p>
                </div>
                <!-- Level 3 -->
                <div class="p-8 rounded-2xl bg-[#121212] border border-gray-800 hover:border-orange-500 transition duration-300 group">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">🔥</div>
                    <h3 class="text-xl font-bold text-orange-500 mb-2">Level 3: Aggressive</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Fighting words. Strong warnings. You are looking for trouble.</p>
                </div>
                <!-- Level 4 -->
                <div class="p-8 rounded-2xl bg-[#121212] border border-red-900/50 hover:border-red-600 transition duration-300 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold px-2 py-1 uppercase">Critical</div>
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">☠️</div>
                    <h3 class="text-xl font-bold text-red-600 mb-2">Level 4: Taboo</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Broadcast banned. The strongest profanity. <span class="text-red-500 font-bold">Use at your own risk.</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl md:text-5xl font-black mb-8 leading-tight">
                        More than just <br>
                        <span class="text-neon-green">bad words.</span>
                    </h2>
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-gray-800 flex items-center justify-center text-2xl">🧠</div>
                            <div class="ml-6">
                                <h3 class="text-xl font-bold text-white mb-2">Cultural Context</h3>
                                <p class="text-gray-400">Why did this word come to be? We explain the history and subculture behind the slang.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-gray-800 flex items-center justify-center text-2xl">🔊</div>
                            <div class="ml-6">
                                <h3 class="text-xl font-bold text-white mb-2">Native Audio</h3>
                                <p class="text-gray-400">Hear exactly how to pronounce it. Intonation matters when you're angry.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-gray-800 flex items-center justify-center text-2xl">⚠️</div>
                            <div class="ml-6">
                                <h3 class="text-xl font-bold text-white mb-2">Situation Examples</h3>
                                <p class="text-gray-400">Real-world scenarios. Know when to use it, and more importantly, when NOT to.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <!-- Mockup / Visual Representation -->
                    <div class="relative rounded-3xl bg-gradient-to-br from-gray-800 to-black border border-gray-700 p-8 shadow-2xl transform md:rotate-3 hover:rotate-0 transition duration-500">
                        <div class="absolute top-4 right-4 text-neon-pink animate-pulse">● Live</div>
                        <h3 class="text-gray-500 uppercase tracking-widest text-xs font-bold mb-2">Word of the day</h3>
                        <h1 class="text-6xl font-black text-white mb-2">헐 <span class="text-2xl text-gray-500 font-normal">/heol/</span></h1>
                        <div class="flex items-center space-x-2 mb-6">
                            <span class="px-2 py-1 bg-neon-blue/20 text-neon-blue text-xs font-bold rounded">Level 1</span>
                            <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs font-bold rounded">Exclamation</span>
                        </div>
                        <p class="text-gray-300 text-lg mb-6">"Oh my god", "No way", "Speechless". Used when you are shocked or surprised.</p>
                        <div class="p-4 bg-gray-900 rounded-xl border border-gray-800">
                            <p class="text-gray-400 italic">"Did you see the news? <strong>Heol...</strong> that's crazy."</p>
                        </div>
                    </div>
                    <!-- Background blob for visual -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-neon-pink to-neon-green rounded-3xl blur-2xl opacity-20 -z-10"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-4xl font-black mb-8">Ready to speak like a local?</h2>
            <p class="text-xl text-gray-400 font-medium">Coming to Android & iOS soon.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-[#0a0a0a] py-12 border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0 text-center md:text-left">
                <span class="text-2xl font-black text-white">kslang<span class="text-neon-pink">.</span></span>
                <p class="text-gray-600 mt-2 text-sm">The Korean your teacher won't teach you.</p>
            </div>
            <div class="flex items-center space-x-8 text-sm text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-white transition">Terms of Service</a>
                <span class="text-gray-400 cursor-default">dntjd207@naver.com</span>
                <a href="#" class="hover:text-white transition" aria-label="Instagram">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.468 2.53c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
