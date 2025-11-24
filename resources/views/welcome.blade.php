<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="kslang - 한국의 욕을 배우고 이해하는 앱">
    
    <title>kslang - 한국의 욕 소개</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #667eea;
        }
        
        /* Hero Section */
        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 4rem 0;
        }
        
        .hero-content h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease-out 0.2s backwards;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.4s backwards;
        }
        
        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        /* Features Section */
        .features {
            background: white;
            padding: 5rem 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: #333;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.8;
        }
        
        /* About Section */
        .about {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 5rem 0;
        }
        
        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .about-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #333;
        }
        
        .about-content p {
            font-size: 1.2rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        footer {
            background: #2d3748;
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        footer p {
            opacity: 0.8;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .nav-links {
                gap: 1rem;
                font-size: 0.9rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="container">
            <div class="logo">kslang</div>
            <ul class="nav-links">
                @if (Route::has('login'))
                    @auth
                        <li><a href="{{ url('/home') }}">홈</a></li>
                    @else
                        <li><a href="{{ route('login') }}">로그인</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}">회원가입</a></li>
                        @endif
                    @endauth
                @endif
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>kslang</h1>
            <p>한국의 욕을 배우고 이해하는 앱</p>
            <div class="cta-buttons">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/home') }}" class="btn btn-primary">시작하기</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">무료로 시작하기</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">로그인</a>
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">주요 기능</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>다양한 욕 모음</h3>
                    <p>한국의 다양한 욕과 표현을 체계적으로 정리하여 제공합니다. 각 욕의 의미와 사용 맥락을 이해할 수 있습니다.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>검색 및 탐색</h3>
                    <p>원하는 욕을 빠르게 검색하고, 카테고리별로 분류된 내용을 쉽게 탐색할 수 있습니다.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💡</div>
                    <h3>상세한 설명</h3>
                    <p>각 욕의 유래, 사용 상황, 강도 등 상세한 정보를 제공하여 올바른 이해를 돕습니다.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <h2>kslang에 대해</h2>
                <p>
                    kslang은 한국의 욕을 체계적으로 소개하고 이해할 수 있도록 돕는 앱입니다.
                </p>
                <p>
                    언어의 한 부분으로 존재하는 욕을 학술적이고 교육적인 관점에서 접근하여,
                    한국어와 한국 문화를 더 깊이 이해할 수 있도록 합니다.
                </p>
                <p>
                    단순히 욕을 나열하는 것이 아니라, 각 표현의 의미와 맥락을 제공하여
                    언어 학습과 문화 이해에 도움이 되도록 설계되었습니다.
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} kslang. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
