# F-000 프로젝트 초기 세팅

## 개요

Laravel 12 기반 kslang 관리 서버의 기반 환경 구성. 공개/관리자/웹뷰 레이아웃 분리, 공통 컴포넌트, 라우트 그룹, 미들웨어, DB 스키마, 시더, 스토리지 설정.

## Routes

### 공개 (web.php)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/` | `landing` | `PublicController@landing` |
| GET | `/privacy` | `privacy` | `PublicController@privacy` |
| GET | `/terms` | `terms` | `PublicController@terms` |

### 관리자 인증 (web.php)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/admin/login` | `admin.login` | `Auth\LoginController@showLoginForm` |
| POST | `/admin/login` | — | `Auth\LoginController@login` |
| POST | `/admin/logout` | `admin.logout` | `Auth\LoginController@logout` |

### 관리자 보호 (web.php, auth 미들웨어)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/admin/dashboard` | `admin.dashboard` | `Admin\DashboardController@index` |

### API v1 (api.php, api.key 미들웨어)
- `/api/v1/slangs`, `/api/v1/categories`, `/api/v1/app/version`, `/api/v1/pages/*`

## 관련 파일

### 모델
- `app/Models/User.php` — login_id 기반 인증
- `app/Models/Category.php` — 카테고리 (slangs M:N 관계)
- `app/Models/Slang.php` — 욕/슬랭 (categories M:N, examples 1:N)
- `app/Models/SlangExample.php` — 예문
- `app/Models/Page.php` — 정적 페이지 (privacy, terms)
- `app/Models/AppSetting.php` — 앱 설정 (key-value)

### 마이그레이션
- `0001_01_01_000000_create_users_table.php`
- `2026_02_28_000001_create_categories_table.php`
- `2026_02_28_000002_create_slangs_table.php`
- `2026_02_28_000003_create_category_slang_table.php`
- `2026_02_28_000004_create_slang_examples_table.php`
- `2026_02_28_000005_create_pages_table.php`
- `2026_02_28_000006_create_app_settings_table.php`

### 미들웨어
- `app/Http/Middleware/ApiKeyMiddleware.php` — X-API-Key 헤더 검증

### 레이아웃
- `resources/views/layouts/public.blade.php` — navbar + content + footer
- `resources/views/layouts/admin.blade.php` — sidebar + header + content
- `resources/views/layouts/webview.blade.php` — content only

### 컴포넌트
- `components/public/navbar.blade.php`, `components/public/footer.blade.php`
- `components/admin/sidebar.blade.php`, `components/admin/header.blade.php`, `components/admin/alert.blade.php`, `components/admin/modal.blade.php`, `components/admin/pagination.blade.php`
- `components/common/button.blade.php`, `components/common/input.blade.php`, `components/common/card.blade.php`

## 핵심 로직

- 관리자 인증: `Auth::attempt(['login_id' => ..., 'password' => ...])` → 세션 기반
- API 인증: `X-API-Key` 헤더 → `config('app.api_key')` 비교
- 레이아웃 분기: `?app=true` 쿼리 → webview 레이아웃, 없으면 public 레이아웃
- 미인증 시 `/admin/login`으로 리다이렉트 (`redirectGuestsTo`)

## API 엔드포인트

`api.key` 미들웨어 적용. 스텁 상태 (F-010에서 구현 예정).

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|---|---|---|
| 2026-02-28 | 초기 세팅 완료 | F-000 |
