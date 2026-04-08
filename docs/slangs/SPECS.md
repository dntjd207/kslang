# 욕/슬랭 관리 (F-003)

## 개요

kslang 서비스의 핵심 콘텐츠인 욕/슬랭 데이터를 등록·수정·삭제하고 표시 순서를 관리하는 기능. 카테고리 연결(다대다), 사용 예문(1:N), 음성 파일(1:1)을 함께 관리하며, 공개 SEO용 상세 페이지에서 사용할 `public_slug`와 영어 메타 정보도 함께 관리한다.

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
| GET | /admin/slangs/{slang}/thread-posts | SlangController@showThreadPosts | admin.slangs.threadPosts.show |
| POST | /admin/slangs/{slang}/generate-thread-posts | SlangController@generateThreadPosts | admin.slangs.threadPosts.generate |
| POST | /admin/slangs/{slang}/generate-audio | SlangController@generateAudio | admin.slangs.generateAudio |
| POST | /admin/slangs/{slang}/generate-example-audio | SlangController@generateExampleAudio | admin.slangs.generateExampleAudio |
| POST | /admin/slangs/{slang}/regenerate-section | SlangController@regenerateSection | admin.slangs.regenerateSection |
| GET | /korean-slang | PublicSlangController@index | slangs.public.index |
| GET | /korean-slang/{slang:public_slug} | PublicSlangController@show | slangs.public.show |

## 관련 파일

### 백엔드
- `app/Models/Slang.php` — Slang 모델 (fillable, casts, 관계, Accessor)
- `app/Models/BlogPost.php` — 공개 슬랭 상세와 연결되는 관련 블로그 관계
- `app/Models/SlangExample.php` — SlangExample 모델
- `app/Http/Controllers/Admin/SlangController.php` — CRUD + reorder + toggle
- `app/Http/Controllers/PublicSlangController.php` — 공개 슬랭 허브/상세
- `app/Services/SlangService.php` — 트랜잭션 기반 생성/수정/삭제 + 슬랭/예문 mp3 생성 서비스
- `app/Services/SlangThreadContentService.php` — Gemini 기반 Thread 포맷 4종 생성 및 저장 서비스
- `app/Services/AudioFileService.php` — 업로드/생성 mp3 저장·삭제·교체·URL 반환 서비스
- `app/Console/Commands/ExpireNewSlangsCommand.php` — 승인 후 3일이 지난 신규 슬랭 표시 해제
- `app/Console/Commands/GenerateSlangSeoCommand.php` — 전체 슬랭 SEO 필드 AI 일괄 생성 (진행 상황 표시)
- `app/Services/SupertoneTtsService.php` — Supertone TTS 합성 서비스
- `app/Http/Requests/Admin/StoreSlangRequest.php` — 생성 유효성 검증
- `app/Http/Requests/Admin/UpdateSlangRequest.php` — 수정 유효성 검증
- `app/Http/Requests/Admin/ReorderSlangRequest.php` — 정렬 유효성 검증
- `app/Http/Requests/Admin/GenerateSlangAudioRequest.php` — 슬랭 본문 mp3 생성 요청 검증
- `app/Http/Requests/Admin/GenerateSlangExampleAudioRequest.php` — 예문 mp3 생성 요청 검증
- `routes/web.php` — 라우트 정의

### 프론트엔드
- `resources/views/admin/slangs/index.blade.php` — 목록 (전체 목록 표시, 검색, 카테고리 탭/필터, 드래그 정렬, 토글, 삭제 모달, Thread 콘텐츠 생성/보기 모달)
- `resources/views/admin/slangs/create.blade.php` — 생성 폼
- `resources/views/admin/slangs/edit.blade.php` — 수정 폼 + 카드뉴스용 복사 버튼
- `resources/views/admin/slangs/_form.blade.php` — 공통 폼 Partial (기본정보 + 카테고리 + 음성 + 예문 include)
- `resources/views/admin/slangs/_audio-upload.blade.php` — 음성 파일 업로드/미리듣기/삭제 Partial
- `resources/views/admin/slangs/_examples.blade.php` — 사용 예문 섹션 Partial
- `resources/views/admin/slangs/_example-row.blade.php` — 단일 예문 행 Partial (라벨, 필드별 에러, 반응형)
- `resources/views/admin/slangs/_form_scripts.blade.php` — 폼 JavaScript (예문 동적 추가/삭제/정렬, 음성 미리듣기, 카드뉴스용 클립보드 복사)
- `resources/views/public/slangs/index.blade.php` — 공개 슬랭 허브 목록 + 검색 hero/하단 앱 CTA
- `resources/views/public/slangs/show.blade.php` — 공개 슬랭 상세 페이지

### 마이그레이션
- `database/migrations/2026_02_28_000002_create_slangs_table.php`
- `database/migrations/2026_02_28_000003_create_category_slang_table.php`
- `database/migrations/2026_02_28_000004_create_slang_examples_table.php`
- `database/migrations/2026_02_28_153809_add_sort_order_index_to_slang_examples_table.php`
- `database/migrations/2026_03_29_144225_add_english_usage_context_to_slangs_table.php`
- `database/migrations/2026_03_30_181911_add_audio_disk_to_slangs_table.php`
- `database/migrations/2026_03_30_181912_add_audio_fields_to_slang_examples_table.php`
- `database/migrations/2026_04_01_093109_add_new_status_fields_to_slangs_table.php`
- `database/migrations/2026_04_01_203734_add_public_seo_fields_to_slangs_table.php`
- `database/migrations/2026_04_06_094111_add_seo_keywords_to_slangs_table.php`
- `database/migrations/2026_04_08_105237_drop_faq_items_from_slangs_table.php`

## 핵심 로직

- **SlangService**: DB 트랜잭션으로 슬랭 + 카테고리 sync + 예문 동기화 + 음성 파일 처리를 원자적으로 실행
- **기본 정보 입력**: 사용 상황은 한글(`usage_context`)과 영어 번역(`english_usage_context`)을 함께 관리
- **공개 SEO 필드**: `public_slug`, `public_title_en`, `public_summary_en`, `seo_title_en`, `seo_description_en`, `seo_keywords_en`을 통해 공개 슬랭 상세 페이지 URL/메타를 관리
- **SEO 필드 AI 생성**: 수정 화면의 `SEO 필드 AI 생성` 버튼으로 현재 폼 기준 `public_slug`, `public_title_en`, `public_summary_en`, `seo_title_en`, `seo_description_en`, `seo_keywords_en`을 생성하며, 결과는 즉시 DB 저장하지 않고 폼에만 반영
- **SEO 자동 생성**: AI 자동 콘텐츠 생성(`fillSlang`) 시 기본 콘텐츠와 함께 SEO 필드(seo_title_en, seo_description_en, seo_keywords_en, public_title_en, public_summary_en)도 자동 생성
- **SEO 코드 최적화**: 공개 슬랭 상세 페이지에 `og:site_name`, `og:locale`, `og:image:width/height/alt`, `article:published_time/modified_time`, `meta keywords`, `robots max-snippet/max-image-preview`, DefinedTerm schema `datePublished/dateModified` 적용
- **SEO 표준 패턴**: 모든 SEO 프롬프트(fillSlang, generateSeoFields)에서 `{한글} ({발음})` 패턴을 공통 메서드(`buildSeoRulesSection`)로 통일하여 Google 한글+로마자 이중 매칭 보장
- **SEO 일괄 생성**: `slang:generate-seo` Artisan 커맨드로 전체 활성 슬랭의 SEO 필드를 순차적으로 AI 생성. `--all` 옵션으로 기존 SEO도 재생성 가능, `--id` 옵션으로 특정 슬랭만 처리 가능, 진행 상황을 실시간 프로그레스바로 표시
- **구조화 데이터**: 공개 슬랭 상세는 `DefinedTerm`, `BreadcrumbList` JSON-LD를 출력하고, 화면에도 Quick facts를 함께 노출해 schema와 실제 콘텐츠가 일치하도록 구성
- **AI 참고 설명**: 상세 등록 시 `ai_generation_hint`에 관리자 설명을 저장하고, AI 자동 생성/재생성 시 최신 유행어 의미 해석의 참고 정보로 사용
- **공개 허브 노출**: `public_slug`가 있고 `apiVisible()` 조건을 만족하는 슬랭만 `/korean-slang` 허브와 공개 상세 페이지에 노출
- **공개 허브 CTA**: `/korean-slang` 허브 상단 hero와 하단 섹션에 Google Play 다운로드 CTA를 노출하고 `data-cta-track` 속성으로 클릭 추적
- **블로그 연결**: 관련 블로그 글과 다대다로 연결되어 슬랭 상세에서 관련 글을 보여주고, 블로그 글에서 슬랭 상세로 내부 링크 가능
- **Thread 콘텐츠 생성/저장**: 목록의 `Thread 생성` 버튼으로 단어별 4가지 Threads 포맷(Word Drop, Did You Know, Korean vs English, Quiz/Poll)을 Gemini로 생성하고 `slangs.thread_post_formats` JSON에 저장. 저장된 포맷은 `Thread 보기` 모달에서 다시 열어 본문/정답 리플을 즉시 복사 가능
- **Thread 콘텐츠 무효화**: 슬랭 본문, 설명, 사용 상황, 예문 등 Thread 생성의 근거 데이터가 수정되면 저장된 Thread 포맷을 자동 초기화하여 오래된 카피가 재사용되지 않도록 함
- **AI 부분 재생성**: 수정 화면에서 설명, 사용 상황, 예문 섹션별로 AI 재생성 가능. 결과는 즉시 DB 저장하지 않고 폼 값만 교체/추가하여 관리자 검토 후 저장
- **신규 단어 표시**: AI 생성 단어를 승인하면 `approved_at`을 기록하고 `is_new=true`로 전환. 승인 후 3일이 지나면 hourly 커맨드가 `is_new=false`로 자동 정리
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
| 2026-03-30 | 목록 화면 Thread 콘텐츠 4포맷 생성/저장 기능 추가 | 행별 버튼, 저장된 포맷 보기/복사 모달, 수정 시 자동 무효화 |
| 2026-04-01 | 신규 단어 상태 관리 추가 | `is_new`, `approved_at` 컬럼과 승인 후 3일 자동 해제 스케줄 추가 |
| 2026-04-01 | 공개 SEO 필드 및 공개 슬랭 허브 추가 | `public_slug`/SEO 메타 필드, `/korean-slang` 목록·상세, 블로그 내부 링크 기반 |
| 2026-04-02 | 공개 SEO 필드 AI 생성 추가 | 수정 화면에서 SEO 메타/slug 생성, 저장 전 폼 반영 방식 유지 |
| 2026-04-02 | 공개 슬랭 상세 디자인/schema 강화 | quick facts, CTA polish, JSON-LD 보강 |
| 2026-04-02 | 공개 슬랭 허브 상단 앱 CTA 추가 | 공식 Play Store 링크 유도 강화 |
| 2026-04-06 | SEO 자동 생성 및 코드 최적화 | AI 생성 시 SEO 필드 자동 포함, `seo_keywords_en` 컬럼 추가, 프롬프트 Google/Bing 최적화, 공개 페이지 OG/schema 메타 강화 |
| 2026-04-08 | FAQ 기능 제거 | `faq_items` 컬럼 삭제, FAQ AI 생성/표시/JSON-LD 전체 제거 |
| 2026-04-06 | SEO 패턴 통일 + 일괄 생성 커맨드 | `{한글} ({발음})` 패턴으로 모든 SEO 프롬프트 통일, `slang:generate-seo` 일괄 생성 커맨드 추가 |
