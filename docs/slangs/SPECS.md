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
| POST | /admin/slangs/{slang}/generate-audio | SlangController@generateAudio | admin.slangs.generateAudio |
| POST | /admin/slangs/{slang}/generate-example-audio | SlangController@generateExampleAudio | admin.slangs.generateExampleAudio |

## 관련 파일

### 백엔드
- `app/Models/Slang.php` — Slang 모델 (fillable, casts, 관계, Accessor)
- `app/Models/SlangExample.php` — SlangExample 모델
- `app/Http/Controllers/Admin/SlangController.php` — CRUD + reorder + toggle
- `app/Services/SlangService.php` — 트랜잭션 기반 생성/수정/삭제 + 슬랭/예문 mp3 생성 서비스
- `app/Services/AudioFileService.php` — 업로드/생성 mp3 저장·삭제·교체·URL 반환 서비스
- `app/Services/SupertoneTtsService.php` — Supertone TTS 합성 서비스
- `app/Http/Requests/Admin/StoreSlangRequest.php` — 생성 유효성 검증
- `app/Http/Requests/Admin/UpdateSlangRequest.php` — 수정 유효성 검증
- `app/Http/Requests/Admin/ReorderSlangRequest.php` — 정렬 유효성 검증
- `app/Http/Requests/Admin/GenerateSlangAudioRequest.php` — 슬랭 본문 mp3 생성 요청 검증
- `app/Http/Requests/Admin/GenerateSlangExampleAudioRequest.php` — 예문 mp3 생성 요청 검증
- `routes/web.php` — 라우트 정의

### 프론트엔드
- `resources/views/admin/slangs/index.blade.php` — 목록 (전체 목록 표시, 검색, 카테고리 탭/필터, 드래그 정렬, 토글, 삭제 모달)
- `resources/views/admin/slangs/create.blade.php` — 생성 폼
- `resources/views/admin/slangs/edit.blade.php` — 수정 폼 + 카드뉴스용 복사 버튼
- `resources/views/admin/slangs/_form.blade.php` — 공통 폼 Partial (기본정보 + 카테고리 + 음성 + 예문 include)
- `resources/views/admin/slangs/_audio-upload.blade.php` — 음성 파일 업로드/미리듣기/삭제 Partial
- `resources/views/admin/slangs/_examples.blade.php` — 사용 예문 섹션 Partial
- `resources/views/admin/slangs/_example-row.blade.php` — 단일 예문 행 Partial (라벨, 필드별 에러, 반응형)
- `resources/views/admin/slangs/_form_scripts.blade.php` — 폼 JavaScript (예문 동적 추가/삭제/정렬, 음성 미리듣기, 카드뉴스용 클립보드 복사)

### 마이그레이션
- `database/migrations/2026_02_28_000002_create_slangs_table.php`
- `database/migrations/2026_02_28_000003_create_category_slang_table.php`
- `database/migrations/2026_02_28_000004_create_slang_examples_table.php`
- `database/migrations/2026_02_28_153809_add_sort_order_index_to_slang_examples_table.php`
- `database/migrations/2026_03_29_144225_add_english_usage_context_to_slangs_table.php`
- `database/migrations/2026_03_30_181911_add_audio_disk_to_slangs_table.php`
- `database/migrations/2026_03_30_181912_add_audio_fields_to_slang_examples_table.php`

## 핵심 로직

- **SlangService**: DB 트랜잭션으로 슬랭 + 카테고리 sync + 예문 동기화 + 음성 파일 처리를 원자적으로 실행
- **기본 정보 입력**: 사용 상황은 한글(`usage_context`)과 영어 번역(`english_usage_context`)을 함께 관리
- **AI 참고 설명**: 상세 등록 시 `ai_generation_hint`에 관리자 설명을 저장하고, AI 자동 생성/재생성 시 최신 유행어 의미 해석의 참고 정보로 사용
- **AI 부분 재생성**: 수정 화면에서 설명, 사용 상황, 예문 섹션별로 AI 재생성 가능. 결과는 즉시 DB 저장하지 않고 폼 값만 교체/추가하여 관리자 검토 후 저장
- **카드뉴스용 복사**: 수정 화면에서 현재 폼 값을 기준으로 단어, 설명, 사용 상황, 예문을 보기 좋은 텍스트로 정리해 클립보드에 복사
- **예문 동기화**: 기존 예문 id가 있으면 업데이트, 없으면 신규 생성, 전송되지 않은 기존 예문은 삭제. FormRequest의 `prepareForValidation()`에서 빈 예문 행 사전 필터링
- **슬랭 본문 mp3 생성**: 수정 화면에서 현재 입력된 한국어 욕을 기준으로 speed 0.8의 Supertone mp3를 생성하고, 생성 직후 `audio_file`, `audio_disk`를 저장해 즉시 재생 가능
- **예문 mp3 생성**: 수정 화면의 각 예문 행에서 개별 mp3 생성 가능. 저장된 예문은 즉시 DB 저장, 새 예문은 hidden input에 경로를 보관했다가 전체 저장 시 함께 반영
- **예문 UI**: 예문 섹션을 `_examples.blade.php` + `_example-row.blade.php`로 분리, 필드별 인라인 에러 표시, 반응형(모바일 세로 스택), 드래그 앤 드롭 정렬, 최대 50개 안내, AI 예문 3개 추가 지원
- **음성 파일**: AudioFileService로 분리. 업로드 파일과 Supertone 생성 mp3를 같은 서비스로 관리하며, 기본 오디오 디스크(`AUDIO_STORAGE_DISK`)에 저장하고 기존 `public` 디스크 파일은 fallback으로 읽음
- **목록 표시**: 관리자 목록은 페이지네이션 없이 전체 데이터를 `sort_order` 오름차순으로 조회
- **검색**: korean, ai_generation_hint, pronunciation, english_description, korean_description, usage_context, english_usage_context 7개 필드 LIKE 검색 (2자 이상)
- **필터**: 레벨(1~4) + 콘텐츠 상태 + 카테고리 탭(whereHas) + 검색어 조합
- **카테고리별 보기**: 카테고리 탭마다 현재 필터 기준 개수를 함께 표시하고, 카테고리 선택 시 해당 카테고리의 슬랭만 즉시 조회
- **삭제**: CASCADE로 category_slang, slang_examples 자동 삭제 + 음성 파일 물리 삭제
- **정렬 제한**: 드래그 정렬은 전체 보기에서만 허용하고, 검색/필터가 적용된 상태에서는 목록 조회만 가능
- **토글/정렬/삭제**: AJAX(Fetch API) + JSON 응답

## API 엔드포인트

해당 기능은 관리자 웹 전용. API는 별도 (Api/V1/SlangController)

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-003 욕/슬랭 관리 기능 구현 | 초기 구현 |
| 2026-02-28 | F-004 사용 예문 관리 개선 | 예문 Partial 분리, 필드별 에러, 반응형, prepareForValidation, sort_order 인덱스 |
| 2026-02-28 | F-005 음성 파일 관리 개선 | AudioFileService 분리, _audio-upload Partial 분리, 드래그 앤 드롭, 미리듣기, AJAX 삭제, destroyAudio 라우트 추가 |
| 2026-03-01 | F-011 AI 자동 콘텐츠 생성 | content_status 컬럼 추가, 빠른 등록/승인/반려 라우트, 상태 필터 탭·뱃지, apiVisible 스코프 |
| 2026-03-29 | 사용 상황 영어 번역 필드 추가 | `english_usage_context` 컬럼, 관리자 입력, API 응답, 검색 확장 |
| 2026-03-29 | 슬랭 수정 화면 AI 섹션 재생성 추가 | 설명/사용 상황 재생성, 예문 3개 추가 생성, 저장 전 폼 반영 |
| 2026-03-30 | 상세 등록(단어+설명) 및 AI 참고 설명 편집 추가 | `ai_generation_hint` 저장, AI 프롬프트 반영, 관리 화면 검색/표시 확장 |
| 2026-03-30 | 슬랭 목록 전체 보기 및 카테고리 탭 추가 | 페이지네이션 제거, 카테고리별 필터 가시성 개선, 필터 상태 정렬 비활성화 |
| 2026-03-30 | 슬랭 본문/예문 mp3 생성 기능 추가 | Supertone speed 0.8 생성, 예문 audio_url API 준비 |
| 2026-03-30 | 슬랭 수정 화면 카드뉴스용 복사 버튼 추가 | 현재 폼 값 기준으로 단어/설명/예문 복사 |
