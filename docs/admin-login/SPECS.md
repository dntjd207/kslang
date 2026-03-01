# F-001 관리자 로그인

## 개요

관리자 페이지(`/admin`)에 접근하기 위한 세션 기반 인증 기능. 관리자는 1명이며, DatabaseSeeder를 통해 초기 계정을 생성한다. Laravel 기본 인증 가드(세션 드라이버)를 활용하여 로그인, 로그아웃, 인증 미들웨어 보호를 구현한다.

## Routes

| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | /admin/login | Auth\LoginController@showLoginForm | guest | 로그인 폼 표시 |
| POST | /admin/login | Auth\LoginController@login | guest, throttle:5,1 | 로그인 처리 |
| POST | /admin/logout | Auth\LoginController@logout | auth | 로그아웃 처리 |
| GET | /admin/dashboard | Admin\DashboardController@index | auth | 대시보드 |

## 관련 파일

- `app/Models/User.php` — User 모델 (login_id 필드 포함)
- `database/migrations/0001_01_01_000000_create_users_table.php` — users 테이블
- `database/seeders/DatabaseSeeder.php` — 관리자 초기 계정 시드
- `app/Http/Controllers/Auth/LoginController.php` — 로그인/로그아웃 컨트롤러
- `app/Http/Controllers/Admin/DashboardController.php` — 대시보드 컨트롤러
- `app/Http/Requests/Auth/LoginRequest.php` — 로그인 유효성 검증
- `routes/web.php` — 라우트 정의
- `bootstrap/app.php` — 미인증 리다이렉트 설정
- `resources/views/layouts/auth.blade.php` — 로그인 전용 레이아웃
- `resources/views/admin/auth/login.blade.php` — 로그인 폼 뷰
- `resources/views/admin/dashboard.blade.php` — 대시보드 뷰
- `resources/views/components/admin/header.blade.php` — 헤더 (로그아웃 버튼)

## 핵심 로직

- `Auth::attempt(['login_id' => ..., 'password' => ...], $remember)`로 인증
- 로그인 성공 시 `session()->regenerate()` 후 `redirect()->intended()` 로 원래 페이지 복귀
- 로그아웃 시 `session()->invalidate()` + `session()->regenerateToken()`
- `guest` 미들웨어: 인증된 사용자 → `/admin/dashboard` 리다이렉트
- `auth` 미들웨어: 미인증 사용자 → `/admin/login` 리다이렉트
- `throttle:5,1`: 1분에 5회 로그인 시도 제한

## API 엔드포인트

해당 없음 (웹 세션 인증만 사용)

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|---------|------|
| 2026-02-28 | F-001 관리자 로그인 구현 | 초기 구현 |
