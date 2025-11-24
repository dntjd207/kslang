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
                    Welcome to <strong>kslang</strong> ("we," "our," or "us"). We are committed to protecting your privacy. 
                    This Privacy Policy explains how we collect, use, and safeguard your information when you visit our website and use our services.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">1. Information We Collect</h2>
                <p class="mb-4">We may collect information about you in a variety of ways. The information we may collect includes:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Personal Data:</strong> Personally identifiable information, such as your name and email address, that you voluntarily give to us when you register with the application or when you choose to participate in various activities related to the application.</li>
                    <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the application, such as your IP address, your browser type, your operating system, your access times, and the pages you have viewed directly before and after accessing the application.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">2. Use of Your Information</h2>
                <p class="mb-4">Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Create and manage your account.</li>
                    <li>Email you regarding your account or order.</li>
                    <li>Fulfill and manage purchases, orders, payments, and other transactions related to the application.</li>
                    <li>Increase the efficiency and operation of the application.</li>
                    <li>Monitor and analyze usage and trends to improve your experience with the application.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">3. Disclosure of Your Information</h2>
                <p>
                    We may share information we have collected about you in certain situations. Your information may be disclosed as follows:
                </p>
                <ul class="list-disc pl-6 space-y-2 mt-4">
                    <li><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others, we may share your information as permitted or required by any applicable law, rule, or regulation.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">4. Security of Your Information</h2>
                <p>
                    We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable, and no method of data transmission can be guaranteed against any interception or other type of misuse.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4 text-white">5. Contact Us</h2>
                <p>
                    If you have questions or comments about this Privacy Policy, please contact us at:
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

