# F-010 앱 API

## 개요

안드로이드 앱에 데이터를 제공하는 REST API. X-API-Key 헤더 기반 인증, JSON 응답, Laravel API Resource 활용, Rate Limiting(분당 60회) 적용. 관리자에서 엔드포인트를 문서 형태로 확인하고 실제 요청 결과를 바로 볼 수 있는 API Playground 화면도 포함한다.

## Routes

| Method | URI | Controller@Action | 설명 |
|--------|-----|-------------------|------|
| GET | /api/v1/slangs | Api\V1\SlangController@index | 욕/슬랭 목록 (필터, 페이지네이션) |
| GET | /api/v1/slangs/search | Api\V1\SlangController@search | 욕/슬랭 검색 |
| GET | /api/v1/slangs/random | Api\V1\SlangController@random | 랜덤 슬랭 (count 파라미터, 최대 10) |
| GET | /api/v1/slangs/daily | Api\V1\SlangController@daily | 오늘의 슬랭 (날짜 기반 고정) |
| GET | /api/v1/slangs/{slang} | Api\V1\SlangController@show | 욕/슬랭 상세 |
| GET | /api/v1/categories | Api\V1\CategoryController@index | 카테고리 목록 |
| GET | /api/v1/categories/{category} | Api\V1\CategoryController@show | 카테고리 상세 + 소속 슬랭 (페이지네이션) |
| GET | /api/v1/app/version | Api\V1\AppController@version | 앱 버전 정보 + Play Store URL |
| GET | /api/v1/app/sync | Api\V1\AppController@sync | 데이터 동기화 정보 (총 개수, 최종 수정일) |
| GET | /api/v1/pages/{slug} | Api\V1\PageController@show | 페이지 조회 (privacy, terms) |

## 관련 파일

### 컨트롤러
- `app/Http/Controllers/Api/V1/SlangController.php`
- `app/Http/Controllers/Api/V1/CategoryController.php`
- `app/Http/Controllers/Api/V1/AppController.php`
- `app/Http/Controllers/Api/V1/PageController.php`
- `app/Http/Controllers/Admin/ApiPlaygroundController.php`

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
- `routes/web.php` — 관리자 API Playground 화면/실행 라우트

### 설정
- `config/app.php` — `api_key` 항목
- `bootstrap/app.php` — API Rate Limiting(분당 60회), 에러 핸들링(401/404/429/500 JSON 응답)

### 관리자 UI / 서비스
- `app/Http/Requests/Admin/ExecuteApiPlaygroundRequest.php`
- `app/Services/ApiPlaygroundService.php`
- `resources/views/admin/api-playground/index.blade.php`
- `resources/views/components/admin/sidebar.blade.php`

## 핵심 로직

### 인증
- 모든 요청에 `X-API-Key` 헤더 필수 (`.env`의 `APP_API_KEY` 값과 일치해야 함)
- 미포함/불일치 시 401 Unauthorized

### Rate Limiting
- 분당 60회 요청 제한 (`throttleApi('60:1')`)
- 초과 시 429 Too Many Requests JSON 응답

### 에러 응답
- 모든 API 에러(`api/*`)에 대해 일관된 JSON 형식 반환
- 401 Unauthorized, 404 Not Found, 429 Too Many Requests, 500 Internal Server Error
- 형식: `{ "error": "...", "message": "..." }`

### 욕/슬랭 목록 (GET /api/v1/slangs)
- `is_active: true` 필터, categories·examples eager loading
- `level` (1~4), `category_id` 선택 필터
- 페이지네이션: `per_page` 기본 20, 최대 100
- `sort_order ASC` 정렬

### 욕/슬랭 검색 (GET /api/v1/slangs/search)
- `q` 파라미터 2자 미만 → 빈 결과
- korean, pronunciation, english_description, korean_description LIKE 검색
- `is_active: true` 필터 + 페이지네이션

### 랜덤 슬랭 (GET /api/v1/slangs/random)
- `count` 파라미터: 1~10 (기본 1)
- count=1이면 단일 SlangResource 반환, 2 이상이면 컬렉션 반환
- `is_active: true` 필터, `inRandomOrder()`

### 오늘의 슬랭 (GET /api/v1/slangs/daily)
- 날짜(Ymd) 기반 시드로 하루 동안 동일한 슬랭 반환
- 활성 슬랭 총 개수 기반 offset 계산 (`date % total`)
- 활성 슬랭 0건 시 404

### 욕/슬랭 상세 (GET /api/v1/slangs/{slang})
- Route Model Binding, 비활성 시 404
- categories·examples eager loading

### 카테고리 목록 (GET /api/v1/categories)
- `withCount`로 활성 슬랭 개수 포함
- `sort_order ASC` 정렬

### 카테고리 상세 (GET /api/v1/categories/{category})
- Route Model Binding으로 카테고리 조회
- 카테고리 정보 + 소속 활성 슬랭 페이지네이션 반환
- 응답 형식: `{ "category": CategoryResource, "slangs": { "data": [...], "links": ..., "meta": ... } }`

### 앱 버전 (GET /api/v1/app/version)
- app_settings에서 min_version, latest_version, play_store_url 조회
- 미설정 시 null 반환

### 데이터 동기화 (GET /api/v1/app/sync)
- slangs: 활성 슬랭 총 개수 + 최종 수정일
- categories: 전체 카테고리 총 개수 + 최종 수정일
- 앱에서 로컬 캐시와 비교하여 데이터 갱신 여부 판단에 활용

### 페이지 (GET /api/v1/pages/{slug})
- slug: privacy, terms만 허용 (where 제약)
- PageResource로 title, content(HTML), updated_at(ISO 8601) 반환

### 관리자 API Playground
- 관리자 인증 후 `/admin/api-playground`에서 전체 엔드포인트 목록, 파라미터, cURL 예시를 한 화면에서 확인
- 브라우저에 실제 API 키를 노출하지 않도록 서버 측 프록시(`POST /admin/api-playground/request`)가 현재 도메인의 `/api/v1/*`로 실제 HTTP 요청 수행
- 엔드포인트별 path/query 파라미터는 allowlist 기반으로 검증하여 허용되지 않은 파라미터 입력 차단
- 응답 결과는 상태 코드, 헤더, JSON 본문, 소요 시간(ms)까지 함께 표시

## API 엔드포인트

총 10개 엔드포인트, 모두 GET 메서드, 모두 ApiKeyMiddleware + Rate Limiting 적용.

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-010 앱 API 구현 | 컨트롤러 4종, API Resource 5종, 라우트, 에러 핸들링 |
| 2026-03-01 | 앱 연동 보강 | Rate Limiting, 에러 JSON 일괄 처리, play_store_url 추가, random/daily/sync/categories/{id} 엔드포인트 추가 |
| 2026-03-29 | 관리자 API Playground 추가 | 엔드포인트 목록 정리, 서버 프록시 기반 실시간 요청 테스트 화면 |
