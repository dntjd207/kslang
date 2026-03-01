# 개인정보처리방침 관리 (F-006)

## 개요

앱과 웹에서 표시할 개인정보처리방침(Privacy Policy) 페이지의 내용을 관리하는 기능. 관리자가 위지윅(WYSIWYG) 에디터(TinyMCE)로 본문을 작성·수정하면, 공개 웹 페이지(`/privacy`)와 앱 웹뷰에서 즉시 반영된다. F-007 이용약관 관리와 동일한 `pages` 테이블 및 관리 구조를 공유한다.

## Routes

### Web
| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | `/privacy` | `PublicController@privacy` | 없음 | 공개 개인정보처리방침 페이지 |
| GET | `/admin/pages/privacy/edit` | `Admin\PageController@edit` | auth | 관리자 수정 폼 |
| PUT | `/admin/pages/privacy` | `Admin\PageController@update` | auth | 관리자 저장 처리 |

### API
| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | `/api/v1/pages/privacy` | `Api\V1\PageController@privacy` | api.key | 앱 API 조회 |

## 관련 파일

| 파일 | 설명 |
|------|------|
| `app/Http/Controllers/PublicController.php` | `privacy()` 메서드 — 공개 페이지 |
| `app/Http/Controllers/Admin/PageController.php` | `edit()`, `update()` — 관리자 CRUD |
| `app/Http/Controllers/Api/V1/PageController.php` | `privacy()`, `terms()` — 앱 API |
| `app/Http/Requests/Admin/UpdatePageRequest.php` | 유효성 검증 FormRequest |
| `app/Models/Page.php` | Page 모델 (F-000에서 생성) |
| `config/purifier.php` | HTMLPurifier 설정 (XSS 방지) |
| `resources/views/public/page.blade.php` | 공개 읽기 전용 페이지 (privacy/terms 공용) |
| `resources/views/admin/pages/edit.blade.php` | 관리자 위지윅 에디터 수정 페이지 (privacy/terms 공용) |
| `routes/web.php` | 웹 라우트 (slug where 제약 포함) |
| `routes/api.php` | API 라우트 |

## 핵심 로직

- **레이아웃 분기**: `?app=true` 쿼리 파라미터 감지 시 `layouts.webview`, 아닐 때 `layouts.public` 사용
- **XSS 방지**: `mews/purifier` 패키지의 `clean()` 함수로 HTML 정화 후 저장
- **slug 제약**: 라우트에 `where('slug', 'privacy|terms')` 적용하여 허용된 페이지만 접근 가능
- **TinyMCE**: CDN 방식으로 위지윅 에디터 로드, 폼 submit 전 `editor.save()` 호출로 textarea 동기화
- **Typography**: `@tailwindcss/typography` 플러그인의 `prose` 클래스로 HTML 본문 타이포그래피 적용

## API 엔드포인트

### GET /api/v1/pages/privacy

**인증**: `X-API-Key` 헤더 필요

**응답 예시**:
```json
{
    "title": "Privacy Policy",
    "content": "<h2>1. Introduction</h2><p>...</p>",
    "updated_at": "2026-02-28T15:30:00+09:00"
}
```

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-006 개인정보처리방침 관리 기능 구현 | 초기 구현 |
| 2026-02-28 | 공개 뷰를 `public/page.blade.php`로 통합 | F-007에서 privacy/terms 공용 뷰로 변경 |
