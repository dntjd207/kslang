<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service | kslang</title>
    
    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { neon: { pink: '#FF0099', green: '#00FF41' } }
                }
            }
        }
    </script>
    <style>
        body { background-color: #121212; color: #e5e5e5; }
        h1, h2, h3 { color: #ffffff; }
        a { color: #FF0099; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body class="antialiased">
    <div class="max-w-3xl mx-auto px-6 py-12 md:py-20">
        <div class="mb-12">
            <a href="{{ route('welcome') }}" class="text-sm font-bold text-gray-500 hover:text-white no-underline mb-4 inline-block">&larr; Back to Home</a>
            <h1 class="text-4xl md:text-5xl font-black mb-4">Terms of Service</h1>
            <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="space-y-10 text-lg leading-relaxed text-gray-300">
            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">1. Agreement to Terms</h2>
                <p>
                    These Terms of Service constitute a legally binding agreement made between you, whether personally or on behalf of an entity ("you") and <strong>kslang</strong> ("we," "us" or "our"), concerning your access to and use of the kslang website as well as any other media form, media channel, mobile website or mobile application related, linked, or otherwise connected thereto (collectively, the "Site").
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">2. Content Warning & Disclaimer</h2>
                <div class="bg-red-900/20 border border-red-800 p-6 rounded-lg mb-4">
                    <p class="text-red-200 font-bold mb-2">⚠️ IMPORTANT NOTICE: MATURE CONTENT</p>
                    <p class="text-sm text-red-100">
                        This application contains strong language, profanity, and slang expressions that may be considered offensive, vulgar, or inappropriate in certain contexts. The content is provided for educational and cultural understanding purposes only.
                    </p>
                </div>
                <p>
                    By using this Site, you acknowledge that you are at least 18 years of age or have reached the age of majority in your jurisdiction. You understand that the translations and definitions provided may include coarse language and are not suitable for formal situations. <strong>kslang</strong> assumes no responsibility for any consequences resulting from your use of these expressions in real-life situations. Use them at your own risk.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">3. User Registration</h2>
                <p>
                    You may be required to register with the Site. You agree to keep your password confidential and will be responsible for all use of your account and password. We reserve the right to remove, reclaim, or change a username you select if we determine, in our sole discretion, that such username is inappropriate, obscene, or otherwise objectionable.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">4. Prohibited Activities</h2>
                <p>
                    You may not access or use the Site for any purpose other than that for which we make the Site available. The Site may not be used in connection with any commercial endeavors except those that are specifically endorsed or approved by us.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">5. Intellectual Property Rights</h2>
                <p>
                    Unless otherwise indicated, the Site is our proprietary property and all source code, databases, functionality, software, website designs, audio, video, text, photographs, and graphics on the Site (collectively, the "Content") and the trademarks, service marks, and logos contained therein (the "Marks") are owned or controlled by us or licensed to us, and are protected by copyright and trademark laws.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">6. Contact Us</h2>
                <p>
                    In order to resolve a complaint regarding the Site or to receive further information regarding use of the Site, please contact us at:
                </p>
                <p class="mt-4 font-bold">
                    <a href="mailto:dntjd207@naver.com">dntjd207@naver.com</a>
                </p>
            </section>
        </div>
        
        <div class="mt-20 pt-10 border-t border-gray-800 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} kslang. All rights reserved.
        </div>
    </div>
</body>
</html>

