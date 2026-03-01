# 이용약관 관리 (F-007)

## 개요

앱과 웹에서 표시할 이용약관(Terms of Service) 페이지의 내용을 관리하는 기능. 관리자가 위지윅(WYSIWYG) 에디터(TinyMCE)로 본문을 작성·수정하면, 공개 웹 페이지(`/terms`)와 앱 웹뷰에서 즉시 반영된다. F-006 개인정보처리방침 관리와 동일한 `pages` 테이블 및 관리 구조를 공유한다.

## Routes

### Web
| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | `/terms` | `PublicController@terms` | 없음 | 공개 이용약관 페이지 |
| GET | `/admin/pages/terms/edit` | `Admin\PageController@edit` | auth | 관리자 수정 폼 |
| PUT | `/admin/pages/terms` | `Admin\PageController@update` | auth | 관리자 저장 처리 |

### API
| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | `/api/v1/pages/terms` | `Api\V1\PageController@terms` | api.key | 앱 API 조회 |

## 관련 파일

| 파일 | 설명 |
|------|------|
| `app/Models/Page.php` | Page 모델 (`$allowedSlugs`, `findBySlug()`, `findBySlugOrFail()`) |
| `app/Http/Controllers/PublicController.php` | `terms()` 메서드 — 공개 페이지 |
| `app/Http/Controllers/Admin/PageController.php` | `edit()`, `update()` — 관리자 CRUD (privacy/terms 공용) |
| `app/Http/Controllers/Api/V1/PageController.php` | `terms()` — 앱 API |
| `app/Http/Requests/Admin/UpdatePageRequest.php` | 유효성 검증 FormRequest |
| `config/purifier.php` | HTMLPurifier 설정 (XSS 방지) |
| `resources/views/public/page.blade.php` | 공개 읽기 전용 페이지 (privacy/terms 공용) |
| `resources/views/admin/pages/edit.blade.php` | 관리자 위지윅 에디터 수정 페이지 (privacy/terms 공용) |
| `routes/web.php` | 웹 라우트 (slug where 제약 포함) |
| `routes/api.php` | API 라우트 |

## 핵심 로직

- **Page 모델 편의 메서드**: `findBySlugOrFail()` 으로 slug 기반 조회, 없으면 404 반환
- **레이아웃 분기**: `?app=true` 쿼리 파라미터 감지 시 `layouts.webview`, 아닐 때 `layouts.public` 사용
- **XSS 방지**: `mews/purifier` 패키지의 `clean()` 함수로 HTML 정화 후 저장
- **slug 제약**: 라우트에 `where('slug', 'privacy|terms')` 적용하여 허용된 페이지만 접근 가능
- **TinyMCE**: CDN 방식으로 위지윅 에디터 로드, 폼 submit 전 `editor.save()` 호출로 textarea 동기화
- **Typography**: `@tailwindcss/typography` 플러그인의 `prose` 클래스로 HTML 본문 타이포그래피 적용
- **공용 뷰**: `public/page.blade.php`로 privacy/terms 공개 페이지를 하나의 뷰로 통합

## API 엔드포인트

### GET /api/v1/pages/terms

**인증**: `X-API-Key` 헤더 필요

**응답 예시**:
```json
{
    "title": "Terms of Service",
    "content": "<h1>Terms of Service</h1><p>By using kslang, you agree to...</p>",
    "updated_at": "2026-02-28T14:30:00+09:00"
}
```

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-007 이용약관 관리 기능 구현 | 초기 구현, F-006 인프라 공유 |
