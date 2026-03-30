# AI 자동 콘텐츠 생성 (F-011)

## 개요

빠른등록(단어만) 또는 상세등록(단어 + 한국어 설명)으로 등록하면 5분 주기 cron이 Google Gemini API를 호출하여 나머지 콘텐츠(발음, 설명, 레벨, 빈도, 사용 상황 한/영, 예문, 카테고리)를 자동 생성. 상세등록의 설명은 최신 유행어/신조어처럼 AI가 의미를 잘 모르는 표현을 보완하는 힌트로 사용된다. 관리자 승인 후에만 API에 노출.

## Routes

| Method | URI | Controller@Action | Name |
|--------|-----|-------------------|------|
| POST | /admin/slangs/detailed-store | SlangController@detailedStore | admin.slangs.detailedStore |
| POST | /admin/slangs/quick-store | SlangController@quickStore | admin.slangs.quickStore |
| PATCH | /admin/slangs/{slang}/approve | SlangController@approve | admin.slangs.approve |
| PATCH | /admin/slangs/{slang}/reject | SlangController@reject | admin.slangs.reject |

## 관련 파일

### 백엔드
- `app/Services/SlangAutoFillService.php` — Gemini API 호출, responseSchema 정의, 데이터 적용
- `app/Console/Commands/AutoFillSlangsCommand.php` — `slang:auto-fill` Artisan 커맨드
- `routes/console.php` — 5분 주기 스케줄 등록
- `app/Http/Requests/Admin/DetailedStoreSlangRequest.php` — 상세 등록 유효성 검증
- `app/Http/Requests/Admin/QuickStoreSlangRequest.php` — 빠른 등록 유효성 검증
- `app/Http/Requests/Admin/RegenerateSlangSectionRequest.php` — 수정 화면 AI 부분 재생성 요청 검증
- `app/Http/Controllers/Admin/SlangController.php` — detailedStore, quickStore, approve, reject, regenerateSection 메서드 추가
- `app/Models/Slang.php` — content_status, ai_generation_hint 필드, apiVisible 스코프 추가

### 마이그레이션
- `database/migrations/2026_03_01_171448_add_content_status_to_slangs_table.php`
- `database/migrations/2026_03_30_131922_add_ai_hint_to_slangs_table.php`

### 프론트엔드
- `resources/views/admin/slangs/index.blade.php` — 빠른 등록/상세 등록 모달, 승인/반려 버튼, 상태 뱃지, 상태 필터 탭
- `resources/views/admin/slangs/_form.blade.php` — AI 참고 설명 입력, 설명/사용 상황 AI 재생성 버튼
- `resources/views/admin/slangs/_examples.blade.php` — AI 예문 3개 추가 버튼
- `resources/views/admin/slangs/_form_scripts.blade.php` — 섹션별 AJAX 재생성 후 폼 값 교체/추가

## 핵심 로직

### content_status 상태 흐름

```
pending → (cron: Gemini API 호출) → generated → (관리자 승인) → approved
                                              → (관리자 반려) → pending (재생성 대기)
수동 등록 → complete (즉시 API 노출 가능)
```

### 상태 값
- `complete`: 관리자가 직접 모든 필드를 채워 생성 (기존 방식)
- `pending`: 빠른등록(단어만) 또는 상세등록(단어 + 설명), AI 생성 대기
- `generated`: AI가 콘텐츠를 생성 완료, 관리자 승인 대기
- `approved`: 관리자가 AI 생성 콘텐츠를 승인

### 상세 등록 / AI 힌트
- 상세 등록은 단일 단어 + 한국어 설명을 함께 저장하며, 설명은 `slangs.ai_generation_hint` 컬럼에 보관
- Gemini 프롬프트는 `ai_generation_hint`가 있으면 이를 최우선 참고 정보로 포함하여 최신 유행어/신조어 의미를 보완
- 수정 화면에서도 AI 참고 설명을 수정할 수 있고, 설명/사용 상황/예문 재생성 시 현재 입력값을 함께 전달

### API 노출 조건
- `is_active = true` AND `content_status IN ('complete', 'approved')`
- `Slang::apiVisible()` 스코프로 통합 적용

### Gemini responseSchema
DB 구조에 맞는 JSON 스키마를 Gemini에 전달하여 구조화된 응답을 받음:
- pronunciation, english_description, korean_description, level, usage_frequency, usage_context, english_usage_context, examples, suggested_categories

### 수정 화면 AI 부분 재생성
- 설명 재생성: `english_description`, `korean_description`만 다시 생성
- 사용 상황 재생성: `usage_context`, `english_usage_context`를 함께 다시 생성
- 예문 추가 생성: 기존 예문을 참고해 새 예문 3개를 추가 생성
- 재생성 결과는 즉시 DB에 저장하지 않고 수정 폼에만 반영하여 관리자 검토 후 저장

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
| 2026-03-29 | 사용 상황 영어 번역 자동 생성 추가 | Gemini 프롬프트/responseSchema에 `english_usage_context` 반영 |
| 2026-03-29 | 수정 화면 AI 섹션 재생성 추가 | 설명/사용 상황 재생성, 예문 3개 추가 생성, 저장 전 검토 방식 |
| 2026-03-30 | 상세 등록(단어+설명) 추가 | `ai_generation_hint` 저장, AI 자동 생성/재생성 프롬프트에 힌트 반영 |
