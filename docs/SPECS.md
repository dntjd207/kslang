# 기술 스택

## 백엔드
- **PHP**: 8.4
- **Laravel**: 12
- **데이터베이스**: MySQL 8

## 프론트엔드
- **TailwindCSS**: 4 (Vite 플러그인 방식)
- **@tailwindcss/typography**: CSS @plugin 방식으로 prose 클래스 사용
- **Vite**: 7
- **Blade**: Laravel 기본 템플릿 엔진
- **TinyMCE**: 6 (CDN, 위지윅 에디터)

## XSS 방지
- **mews/purifier**: 3.4 (HTMLPurifier 래퍼, HTML 콘텐츠 정화)

## 외부 API
- **Google Gemini API**: gemini-3.1-pro-preview 모델, Thinking 지원, 선택적 responseSchema

## 개발 도구
- **Pest**: 4 (테스트)
- **Pint**: 1 (코드 포맷터)
- **Laravel Sail**: 1
- **Laravel Boost**: 2 (MCP)
- **Laravel Pail**: 1 (로그)

## 서버 환경
- **Laravel Herd**: 로컬 개발 서버 (http://kslang.test)

## 인증
- **관리자**: Laravel 기본 세션 인증 (login_id + password)
- **API**: X-API-Key 헤더 인증 (ApiKeyMiddleware)
