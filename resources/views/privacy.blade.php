<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy | kslang</title>
    
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
            <h1 class="text-4xl md:text-5xl font-black mb-4">Privacy Policy</h1>
            <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="space-y-10 text-lg leading-relaxed text-gray-300">
            <section>
                <p>
                    Welcome to <strong>kslang</strong>. This Privacy Policy explains how we handle your information.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">1. Information We Collect</h2>
                <p>When you sign in with Google, we collect the following information from your Google account:</p>
                <ul class="list-disc pl-6 space-y-2 mt-4">
                    <li>Name</li>
                    <li>Email address</li>
                    <li>Profile picture</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">2. How We Use Your Information</h2>
                <p>We use your information solely to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-4">
                    <li>Create and manage your account</li>
                    <li>Provide our services to you</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">3. Data Retention</h2>
                <p>
                    When you delete your account, your personal data will be retained for <strong>3 months</strong> and then permanently deleted. This retention period allows for account recovery if needed.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">4. Contact Us</h2>
                <p>
                    If you have any questions about this Privacy Policy, please contact us at:
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

