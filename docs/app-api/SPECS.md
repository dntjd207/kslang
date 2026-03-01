# F-010 앱 API

## 개요

안드로이드 앱에 데이터를 제공하는 REST API. X-API-Key 헤더 기반 인증, JSON 응답, Laravel API Resource 활용.

## Routes

| Method | URI | Controller@Action | 설명 |
|--------|-----|-------------------|------|
| GET | /api/v1/slangs | Api\V1\SlangController@index | 욕/슬랭 목록 (필터, 페이지네이션) |
| GET | /api/v1/slangs/search | Api\V1\SlangController@search | 욕/슬랭 검색 |
| GET | /api/v1/slangs/{slang} | Api\V1\SlangController@show | 욕/슬랭 상세 |
| GET | /api/v1/categories | Api\V1\CategoryController@index | 카테고리 목록 |
| GET | /api/v1/app/version | Api\V1\AppController@version | 앱 버전 정보 |
| GET | /api/v1/pages/{slug} | Api\V1\PageController@show | 페이지 조회 (privacy, terms) |

## 관련 파일

### 컨트롤러
- `app/Http/Controllers/Api/V1/SlangController.php`
- `app/Http/Controllers/Api/V1/CategoryController.php`
- `app/Http/Controllers/Api/V1/AppController.php`
- `app/Http/Controllers/Api/V1/PageController.php`

### API Resource
- `app/Http/Resources/Api/V1/SlangResource.php`
- `app/Http/Resources/Api/V1/SlangCategoryResource.php`
- `app/Http/Resources/Api/V1/SlangExampleResource.php`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Http/Resources/Api/V1/PageResource.php`

### 미들웨어
- `app/Http/Middleware/ApiKeyMiddleware.php` — X-API-Key 헤더 검증

### 라우트
- `routes/api.php` — v1 프리픽스 + ApiKeyMiddleware 그룹

### 설정
- `config/app.php` — `api_key` 항목
- `bootstrap/app.php` — API 404 에러 핸들링

## 핵심 로직

### 인증
- 모든 요청에 `X-API-Key` 헤더 필수 (`.env`의 `APP_API_KEY` 값과 일치해야 함)
- 미포함/불일치 시 401 Unauthorized

### 욕/슬랭 목록 (GET /api/v1/slangs)
- `is_active: true` 필터, categories·examples eager loading
- `level` (1~4), `category_id` 선택 필터
- 페이지네이션: `per_page` 기본 20, 최대 100
- `sort_order ASC` 정렬

### 욕/슬랭 검색 (GET /api/v1/slangs/search)
- `q` 파라미터 2자 미만 → 빈 결과
- korean, pronunciation, english_description, korean_description LIKE 검색
- `is_active: true` 필터 + 페이지네이션

### 욕/슬랭 상세 (GET /api/v1/slangs/{slang})
- Route Model Binding, 비활성 시 404
- categories·examples eager loading

### 카테고리 목록 (GET /api/v1/categories)
- `withCount`로 활성 슬랭 개수 포함
- `sort_order ASC` 정렬

### 앱 버전 (GET /api/v1/app/version)
- app_settings에서 min_version, latest_version 조회
- 미설정 시 null 반환

### 페이지 (GET /api/v1/pages/{slug})
- slug: privacy, terms만 허용 (where 제약)
- PageResource로 title, content(HTML), updated_at(ISO 8601) 반환

## API 엔드포인트

총 6개 엔드포인트, 모두 GET 메서드, 모두 ApiKeyMiddleware 적용.

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-010 앱 API 구현 | 컨트롤러 4종, API Resource 5종, 라우트, 에러 핸들링 |
