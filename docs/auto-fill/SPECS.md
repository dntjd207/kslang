# AI 자동 콘텐츠 생성 (F-011)

## 개요

단어(korean)만 등록하면 5분 주기 cron이 Google Gemini API를 호출하여 나머지 콘텐츠(발음, 설명, 레벨, 빈도, 맥락, 예문, 카테고리)를 자동 생성. 관리자 승인 후에만 API에 노출.

## Routes

| Method | URI | Controller@Action | Name |
|--------|-----|-------------------|------|
| POST | /admin/slangs/quick-store | SlangController@quickStore | admin.slangs.quickStore |
| PATCH | /admin/slangs/{slang}/approve | SlangController@approve | admin.slangs.approve |
| PATCH | /admin/slangs/{slang}/reject | SlangController@reject | admin.slangs.reject |

## 관련 파일

### 백엔드
- `app/Services/SlangAutoFillService.php` — Gemini API 호출, responseSchema 정의, 데이터 적용
- `app/Console/Commands/AutoFillSlangsCommand.php` — `slang:auto-fill` Artisan 커맨드
- `routes/console.php` — 5분 주기 스케줄 등록
- `app/Http/Requests/Admin/QuickStoreSlangRequest.php` — 빠른 등록 유효성 검증
- `app/Http/Controllers/Admin/SlangController.php` — quickStore, approve, reject 메서드 추가
- `app/Models/Slang.php` — content_status 필드, apiVisible 스코프 추가

### 마이그레이션
- `database/migrations/2026_03_01_171448_add_content_status_to_slangs_table.php`

### 프론트엔드
- `resources/views/admin/slangs/index.blade.php` — 빠른 등록 모달, 승인/반려 버튼, 상태 뱃지, 상태 필터 탭

## 핵심 로직

### content_status 상태 흐름

```
pending → (cron: Gemini API 호출) → generated → (관리자 승인) → approved
                                              → (관리자 반려) → pending (재생성 대기)
수동 등록 → complete (즉시 API 노출 가능)
```

### 상태 값
- `complete`: 관리자가 직접 모든 필드를 채워 생성 (기존 방식)
- `pending`: 단어만 등록, AI 생성 대기
- `generated`: AI가 콘텐츠를 생성 완료, 관리자 승인 대기
- `approved`: 관리자가 AI 생성 콘텐츠를 승인

### API 노출 조건
- `is_active = true` AND `content_status IN ('complete', 'approved')`
- `Slang::apiVisible()` 스코프로 통합 적용

### Gemini responseSchema
DB 구조에 맞는 JSON 스키마를 Gemini에 전달하여 구조화된 응답을 받음:
- pronunciation, english_description, korean_description, level, usage_frequency, usage_context, examples, suggested_categories

### 카테고리 자동 매칭
- 프롬프트에 현재 등록된 카테고리 목록을 포함
- Gemini가 suggested_categories로 적합한 카테고리를 추천
- DB에 존재하는 카테고리만 자동 연결

### Cron 스케줄
- 5분마다 실행: `Schedule::command('slang:auto-fill --limit=5')->everyFiveMinutes()`
- 한 번에 최대 5건 처리 (API 부하 분산)

## API 엔드포인트

해당 기능은 관리자 웹 + Artisan 커맨드. API 변경은 기존 엔드포인트에 apiVisible 스코프 적용.

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-03-01 | F-011 AI 자동 콘텐츠 생성 기능 구현 | 초기 구현 |
