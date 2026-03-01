# 욕/슬랭 관리 (F-003)

## 개요

kslang 서비스의 핵심 콘텐츠인 욕/슬랭 데이터를 등록·수정·삭제하고 표시 순서를 관리하는 기능. 카테고리 연결(다대다), 사용 예문(1:N), 음성 파일(1:1)을 함께 관리.

## Routes

| Method | URI | Controller@Action | Name |
|--------|-----|-------------------|------|
| GET | /admin/slangs | SlangController@index | admin.slangs.index |
| GET | /admin/slangs/create | SlangController@create | admin.slangs.create |
| POST | /admin/slangs | SlangController@store | admin.slangs.store |
| GET | /admin/slangs/{slang}/edit | SlangController@edit | admin.slangs.edit |
| PUT | /admin/slangs/{slang} | SlangController@update | admin.slangs.update |
| DELETE | /admin/slangs/{slang} | SlangController@destroy | admin.slangs.destroy |
| POST | /admin/slangs/reorder | SlangController@reorder | admin.slangs.reorder |
| PATCH | /admin/slangs/{slang}/toggle | SlangController@toggle | admin.slangs.toggle |
| DELETE | /admin/slangs/{slang}/audio | SlangController@destroyAudio | admin.slangs.destroyAudio |

## 관련 파일

### 백엔드
- `app/Models/Slang.php` — Slang 모델 (fillable, casts, 관계, Accessor)
- `app/Models/SlangExample.php` — SlangExample 모델
- `app/Http/Controllers/Admin/SlangController.php` — CRUD + reorder + toggle
- `app/Services/SlangService.php` — 트랜잭션 기반 생성/수정/삭제 서비스
- `app/Services/AudioFileService.php` — 음성 파일 저장/삭제/교체/URL 반환 서비스
- `app/Http/Requests/Admin/StoreSlangRequest.php` — 생성 유효성 검증
- `app/Http/Requests/Admin/UpdateSlangRequest.php` — 수정 유효성 검증
- `app/Http/Requests/Admin/ReorderSlangRequest.php` — 정렬 유효성 검증
- `routes/web.php` — 라우트 정의

### 프론트엔드
- `resources/views/admin/slangs/index.blade.php` — 목록 (검색, 필터, 드래그 정렬, 토글, 삭제 모달)
- `resources/views/admin/slangs/create.blade.php` — 생성 폼
- `resources/views/admin/slangs/edit.blade.php` — 수정 폼
- `resources/views/admin/slangs/_form.blade.php` — 공통 폼 Partial (기본정보 + 카테고리 + 음성 + 예문 include)
- `resources/views/admin/slangs/_audio-upload.blade.php` — 음성 파일 업로드/미리듣기/삭제 Partial
- `resources/views/admin/slangs/_examples.blade.php` — 사용 예문 섹션 Partial
- `resources/views/admin/slangs/_example-row.blade.php` — 단일 예문 행 Partial (라벨, 필드별 에러, 반응형)
- `resources/views/admin/slangs/_form_scripts.blade.php` — 폼 JavaScript (예문 동적 추가/삭제/정렬, 음성 미리듣기)

### 마이그레이션
- `database/migrations/2026_02_28_000002_create_slangs_table.php`
- `database/migrations/2026_02_28_000003_create_category_slang_table.php`
- `database/migrations/2026_02_28_000004_create_slang_examples_table.php`
- `database/migrations/2026_02_28_153809_add_sort_order_index_to_slang_examples_table.php`

## 핵심 로직

- **SlangService**: DB 트랜잭션으로 슬랭 + 카테고리 sync + 예문 동기화 + 음성 파일 처리를 원자적으로 실행
- **예문 동기화**: 기존 예문 id가 있으면 업데이트, 없으면 신규 생성, 전송되지 않은 기존 예문은 삭제. FormRequest의 `prepareForValidation()`에서 빈 예문 행 사전 필터링
- **예문 UI**: 예문 섹션을 `_examples.blade.php` + `_example-row.blade.php`로 분리, 필드별 인라인 에러 표시, 반응형(모바일 세로 스택), 드래그 앤 드롭 정렬, 최대 50개 안내
- **음성 파일**: AudioFileService로 분리. UUID 파일명으로 `storage/app/public/audio/slangs/`에 저장. 드래그 앤 드롭 + 파일 선택 업로드, 클라이언트/서버 유효성 검증(mp3, 5MB), 미리듣기, AJAX 단독 삭제, 교체 시 기존 파일 물리 삭제
- **검색**: korean, pronunciation, english_description, korean_description, usage_context 5개 필드 LIKE 검색 (2자 이상)
- **필터**: 레벨(1~4) + 카테고리(whereHas) + 검색어 조합
- **삭제**: CASCADE로 category_slang, slang_examples 자동 삭제 + 음성 파일 물리 삭제
- **토글/정렬/삭제**: AJAX(Fetch API) + JSON 응답

## API 엔드포인트

해당 기능은 관리자 웹 전용. API는 별도 (Api/V1/SlangController)

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-003 욕/슬랭 관리 기능 구현 | 초기 구현 |
| 2026-02-28 | F-004 사용 예문 관리 개선 | 예문 Partial 분리, 필드별 에러, 반응형, prepareForValidation, sort_order 인덱스 |
| 2026-02-28 | F-005 음성 파일 관리 개선 | AudioFileService 분리, _audio-upload Partial 분리, 드래그 앤 드롭, 미리듣기, AJAX 삭제, destroyAudio 라우트 추가 |
