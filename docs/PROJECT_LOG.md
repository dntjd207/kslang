# 프로젝트 작업 로그

## 2026-02-28

### 작업 내용
- F-000 프로젝트 초기 세팅 완료
  - .env 설정 (MySQL, Asia/Seoul 타임존, APP_API_KEY)
  - 모델 6종 생성: User, Category, Slang, SlangExample, Page, AppSetting
  - 마이그레이션 7종 생성 및 실행: users(login_id 추가), categories, slangs, category_slang, slang_examples, pages, app_settings
  - ApiKeyMiddleware 생성 및 bootstrap/app.php에 등록
  - 컨트롤러 13종 생성: PublicController, Auth/LoginController, Admin 4종, Api/V1 4종
  - 라우트 구성: web.php (공개 + 관리자), api.php (API v1)
  - Blade 레이아웃 3종: public, admin, webview
  - 공통 컴포넌트 10종: navbar, footer, sidebar, header, alert, modal, pagination, button, input, card
  - 에러 페이지: 404, 500
  - DatabaseSeeder (관리자 계정, pages 2건, app_settings 3건)
  - 스토리지 설정: audio/slangs 디렉토리 + storage:link
  - TailwindCSS 4 + Vite 7 빌드 완료

### 주요 결정 사항
- TailwindCSS 4 사용 (기존 프로젝트 설정 유지, tailwind.config.js 대신 @tailwindcss/vite 플러그인 방식)
- 관리자 인증은 login_id + password 방식 (Laravel 기본 auth 미들웨어)
- API 인증은 X-API-Key 헤더 방식 (커스텀 ApiKeyMiddleware)
- ?app=true 쿼리 파라미터로 웹뷰/공개 레이아웃 분기

---

### 작업 내용
- F-001 관리자 로그인 기능 구현
  - LoginRequest FormRequest 생성 (login_id, password 유효성 검증 + 한국어 메시지)
  - LoginController 수정: LoginRequest 사용, 뷰 경로 변경, 에러 메시지 한국어화
  - routes/web.php 재구성: guest 미들웨어(로그인 라우트), throttle:5,1(로그인 POST), auth(관리자 라우트)
  - bootstrap/app.php: redirectUsersTo('/admin/dashboard') 추가 (인증된 사용자 guest 라우트 접근 시 리다이렉트)
  - layouts/auth.blade.php 로그인 전용 레이아웃 생성
  - admin/auth/login.blade.php 로그인 폼 뷰 생성 (한국어 UI, 에러 표시, old() 유지, remember 체크박스)
  - header.blade.php 로그아웃 버튼 한국어화

### 주요 결정 사항
- 로그인 페이지는 독립 레이아웃(layouts/auth.blade.php) 사용 — 사이드바/헤더 없음
- 라우트 그룹을 admin 하나로 통합하여 가독성 향상
- 색상은 기존 indigo-600 유지 (디자인 가이드라인 primary 색상과 일치)

---

### 작업 내용
- F-002 카테고리 관리 기능 구현
  - Category 모델에 casts() 메서드 추가 (sort_order → integer)
  - CategoryController 구현: index, store, update, destroy, reorder (AJAX JSON 응답)
  - FormRequest 3종 생성: StoreCategoryRequest, UpdateCategoryRequest, ReorderCategoryRequest
  - routes/web.php 카테고리 리소스 라우트를 only()로 변경 (index, store, update, destroy)
  - admin/categories/index.blade.php 생성: 목록, 생성/수정 모달, 삭제 확인 모달, 빈 상태 UI
  - SortableJS(CDN) 연동: 드래그 앤 드롭 정렬 + AJAX reorder 저장
  - 토스트 알림 시스템: 성공/에러 메시지 우상단 표시 (슬라이드 인/아웃 애니메이션)
  - AJAX 기반 CRUD: Fetch API + CSRF 토큰 + JSON 유효성 에러 표시

### 주요 결정 사항
- 카테고리 관리는 단일 페이지(SPA-like) 구성 — 모달 + AJAX로 별도 페이지 없이 처리
- SortableJS는 CDN 방식 사용 (npm 대신, 빌드 복잡도 방지)
- 기존 모달 컴포넌트 대신 직접 HTML 구성 (동적 제목 변경 필요)

---

### 작업 내용
- F-003 욕/슬랭 관리 기능 구현
  - Slang 모델에 level_korean_label Accessor 추가
  - SlangService 생성: DB 트랜잭션 기반 생성/수정/삭제 (카테고리 sync, 예문 동기화, 음성 파일 처리)
  - StoreSlangRequest, UpdateSlangRequest, ReorderSlangRequest FormRequest 3종 생성
  - SlangController 구현: index(검색/필터/페이지네이션), create, store, edit, update, destroy, reorder, toggle
  - routes/web.php에 slangs/reorder 라우트를 resource 앞에 배치하여 와일드카드 충돌 방지
  - index.blade.php: 검색(debounce 500ms), 레벨 필터 탭, 카테고리 드롭다운, 드래그 앤 드롭 정렬, 활성 토글, 삭제 모달
  - create/edit.blade.php: 2단 레이아웃(기본정보 + 카테고리/음성), 예문 동적 추가/삭제/드래그 정렬
  - _form.blade.php: 공통 폼 Partial (기본 정보, 카테고리 체크박스, 음성 업로드, 예문 관리)
  - _form_scripts.blade.php: 예문 동적 관리, 음성 미리듣기, 드래그 앤 드롭 파일 업로드

### 주요 결정 사항
- 욕/슬랭 관리는 별도 페이지 구성 (CRUD 각각 독립 페이지) — 카테고리와 달리 입력 필드가 많아 모달 부적합
- SlangService로 비즈니스 로직 분리 — Controller는 얇게 유지
- 예문 동기화는 incoming ID 비교 방식으로 추가/수정/삭제를 한 번에 처리
- SortableJS CDN 방식 유지 (카테고리와 동일)
- 폼 JavaScript는 _form_scripts.blade.php로 분리하여 create/edit에서 공유

---

### 작업 내용
- F-004 사용 예문 관리 개선
  - SlangExample 모델에 `casts()` 메서드 추가 (sort_order → integer)
  - slang_examples 테이블에 sort_order 인덱스 추가 마이그레이션 생성 및 실행
  - StoreSlangRequest/UpdateSlangRequest에 `prepareForValidation()` 추가 (빈 예문 행 사전 필터링)
  - StoreSlangRequest에 `examples.*.id` 유효성 검증 규칙 추가
  - 예문 섹션을 `_examples.blade.php` + `_example-row.blade.php`로 Partial 분리
  - 예문 행에 라벨(한국어 예문/영어 번역) 추가
  - 필드별 인라인 에러 메시지 표시 (`@error` 디렉티브)
  - 반응형 디자인: 모바일에서 한국어/영어 입력란 세로 스택 (flex-col → sm:flex-row)
  - 빈 상태 안내 메시지 개선 (border-dashed 스타일)
  - 삭제 아이콘 변경 (휴지통 → X 아이콘)
  - 예문 추가 시 첫 입력란 자동 포커스
  - 최대 50개 초과 시 안내 메시지 표시

### 주요 결정 사항
- 예문 섹션을 별도 Blade Partial로 분리하여 재사용성 향상
- 에러 메시지를 하단 그룹 표시에서 필드별 인라인으로 변경하여 UX 개선
- prepareForValidation()으로 빈 행을 사전 필터링하여 불필요한 유효성 검증 방지

---

### 작업 내용
- F-005 음성 파일 관리 기능 구현
  - AudioFileService 생성: 음성 파일 저장/삭제/교체/URL 반환 로직 캡슐화
  - Slang 모델 보강: hasAudioFile(), deleteAudioFile() 메서드, getAudioUrlAttribute() Storage::disk 방식으로 변경
  - SlangService 리팩토링: AudioFileService 의존성 주입으로 음성 파일 처리 위임
  - SlangController에 destroyAudio() 메서드 추가 (AJAX 음성 파일 단독 삭제)
  - routes/web.php에 DELETE /admin/slangs/{slang}/audio 라우트 추가
  - _audio-upload.blade.php Partial 생성: 3가지 상태 UI (드롭존, 미리듣기, 기존 파일), 삭제 확인 모달
  - _form.blade.php에서 기존 음성 섹션을 새 Partial로 교체
  - _form_scripts.blade.php 음성 JS 전면 재작성: 클라이언트 유효성 검증, 드래그 앤 드롭, 미리듣기 플레이어, AJAX 삭제, 토스트 알림

### 주요 결정 사항
- 음성 파일 처리 로직을 AudioFileService로 분리하여 단일 책임 원칙 준수
- 음성 파일 삭제는 AJAX 방식으로 구현하여 폼 전체를 재제출하지 않아도 됨
- 클라이언트 측 유효성 검증(확장자, MIME, 크기)과 서버 측 검증을 이중으로 적용

---

### 작업 내용
- F-006 개인정보처리방침 관리 기능 구현
  - `mews/purifier` 패키지 설치 및 `config/purifier.php` 허용 태그 설정 (XSS 방지)
  - `@tailwindcss/typography` 플러그인 설치 및 `app.css`에 `@plugin` 등록
  - `PublicController@privacy` 메서드 개선: `$request->boolean('app')`으로 레이아웃 분기
  - `Admin\PageController` 구현: `edit()` (위지윅 에디터 수정 폼), `update()` (HTMLPurifier 정화 후 저장)
  - `UpdatePageRequest` FormRequest 생성 (content required, max:1000000)
  - `Api\V1\PageController` 구현: `privacy()`, `terms()` — JSON 응답 (title, content, updated_at)
  - `routes/web.php`에 slug `where('slug', 'privacy|terms')` 제약 조건 추가
  - `public/privacy.blade.php` 재작성: 타이포그래피(prose), 마지막 수정일, 빈 콘텐츠 안내
  - `admin/pages/edit.blade.php` 생성: TinyMCE CDN 에디터, 공개 페이지 보기 링크, 마지막 수정 시각
  - `public/terms.blade.php`도 동일 스타일로 개선 (F-007과 공유)

### 주요 결정 사항
- TinyMCE는 CDN 방식 로드 (no-api-key, 운영 시 교체 필요)
- `admin/pages/edit.blade.php`는 privacy/terms 공용 뷰로 구성 ($page->slug로 분기)
- HTMLPurifier로 저장 시 XSS 위험 태그 필터링, prose 클래스로 공개 페이지 타이포그래피 적용
- TailwindCSS 4에서는 `tailwind.config.js` 대신 CSS `@plugin` 방식으로 typography 플러그인 등록

---

### 작업 내용
- F-007 이용약관 관리 기능 구현
  - `Page` 모델에 편의 메서드 추가: `$allowedSlugs`, `findBySlug()`, `findBySlugOrFail()`
  - `PublicController`, `Admin\PageController`, `Api\V1\PageController`를 모델 편의 메서드 사용으로 리팩토링
  - `public/privacy.blade.php`와 `public/terms.blade.php`를 공용 `public/page.blade.php`로 통합
  - F-006과 동일한 인프라 공유: 라우트, 관리자 수정 뷰, TinyMCE 에디터, HTMLPurifier, 시더

### 주요 결정 사항
- F-006에서 이미 구축된 인프라(라우트, 컨트롤러, 뷰, 시더 등)를 그대로 활용
- 개별 공개 뷰(privacy.blade.php, terms.blade.php)를 공용 page.blade.php로 통합하여 중복 제거
- Page 모델에 `findBySlugOrFail()` 편의 메서드를 추가하여 컨트롤러 코드 간소화

---

### 작업 내용
- F-008 앱 버전 관리 기능 구현
  - AppSetting 모델에 `getMultiple()` 헬퍼 메서드 추가
  - `UpdateAppSettingRequest` FormRequest 생성: 시맨틱 버저닝 정규식 검증 + `version_compare()` 교차 검증
  - `Admin\AppSettingController` 구현: edit() 설정 폼 렌더링, update() 설정 저장
  - `Api\V1\AppController` 구현: version() 앱 버전 정보 JSON 응답
  - `admin/app-settings/edit.blade.php` 뷰 생성: 버전 입력 폼 + 안내 카드

### 주요 결정 사항
- 라우트, 사이드바, 시더는 F-000 초기 세팅에서 이미 구성되어 있어 그대로 활용
- 입력 필드에 도움말 텍스트를 포함하여 각 설정의 역할을 명확히 안내
- API 응답에 `play_store_url`은 포함하지 않음 (앱에서 별도 관리)

---

### 작업 내용
- F-010 앱 API 구현
  - API Resource 5종 생성: SlangResource, SlangCategoryResource, SlangExampleResource, CategoryResource, PageResource
  - Api\V1\SlangController 구현: index(목록, level·category_id 필터, 페이지네이션), show(상세, 비활성 404), search(LIKE 검색, 2자 미만 빈 결과)
  - Api\V1\CategoryController 구현: index(withCount 활성 슬랭, sort_order 정렬)
  - Api\V1\AppController 리팩토링: getMultiple() → whereIn/pluck 직접 사용, 미설정 시 null 반환
  - Api\V1\PageController 리팩토링: privacy()/terms() 개별 메서드 → show(string $slug) 통합, PageResource 사용
  - routes/api.php 업데이트: Route Model Binding({slang}), pages/{slug} where 제약 적용
  - bootstrap/app.php: API 라우트 NotFoundHttpException → JSON 404 응답 핸들링 추가

### 주요 결정 사항
- 기존 개별 privacy()/terms() 메서드를 show(string $slug)로 통합하여 중복 제거
- AppController에서 미설정 값을 '1.0.0' 기본값 대신 null로 반환 (명세 준수)
- Laravel API Resource를 활용하여 일관된 JSON 응답 구조 유지
- per_page 파라미터 최대 100 제한, 0 이하 시 기본값 20 적용

---

### 작업 내용
- F-009 랜딩 페이지 구현
  - `LandingController` 생성: slangs(level 1~2, active, limit 8) 조회 + AppSetting play_store_url 조회
  - `routes/web.php` 라우트 변경: `PublicController@landing` → `LandingController@index`
  - `PublicController`에서 `landing()` 메서드 제거
  - 메인 뷰 `public/landing.blade.php` + 파셜 4종 생성 (hero, features, preview, download)
  - 히어로 섹션: 그라디언트 배경, 앱 소개, Google Play 다운로드 버튼
  - 앱 소개 섹션: 4가지 기능 카드 그리드 (4-Level, Audio, Examples, Categories)
  - 미리보기 섹션: slang 카드 (레벨 뱃지, 한국어, 발음, 설명), 0건 시 안내 메시지
  - 다운로드 CTA 섹션: 강조 배경 + 다운로드 유도
  - play_store_url 미설정 시 "Coming Soon" 버튼 표시

### 주요 결정 사항
- `PublicController`에서 별도 `LandingController`로 분리하여 관심사 분리
- AppSetting::getValue() 헬퍼 활용하여 play_store_url 조회
- 섹션별 파셜 분리로 가독성과 유지보수성 확보

---

## 2026-03-01

### 작업 내용
- 앱 연동 API 보강 (6개 → 10개 엔드포인트)
  - `GET /api/v1/app/version` 응답에 `play_store_url` 추가 (강제 업데이트 시 스토어 이동용)
  - API Rate Limiting 적용: `throttleApi('60:1')` — 분당 60회 제한
  - API 에러 응답 일관성 확보: 401/404/429/500 + 기타 HttpException 모두 JSON 형식 반환
  - `GET /api/v1/app/sync` 데이터 동기화 엔드포인트 추가 (슬랭/카테고리 총 개수 + 최종 수정일)
  - `GET /api/v1/slangs/random` 랜덤 슬랭 엔드포인트 추가 (count 파라미터, 최대 10개)
  - `GET /api/v1/slangs/daily` 오늘의 슬랭 엔드포인트 추가 (날짜 기반 시드로 하루 동안 고정)
  - `GET /api/v1/categories/{category}` 카테고리 상세 엔드포인트 추가 (카테고리 정보 + 소속 슬랭 페이지네이션)

### 주요 결정 사항
- Rate Limiting은 Laravel 기본 `throttleApi` 사용 (분당 60회)
- 오늘의 슬랭은 date(Ymd) % totalActive 방식으로 날짜별 고정 (DB 추가 필드 없이 구현)
- 랜덤 슬랭의 count 최대값은 10으로 제한 (성능 고려)
- 카테고리 상세는 category 정보와 slangs 페이지네이션을 한 응답에 포함

---

### 작업 내용
- Google Gemini API 연동 서비스 구현
  - `GeminiService`: 일반/스트리밍 요청, thinkingConfig, 선택적 responseSchema 지원
  - `GeminiResponse` DTO: 응답 텍스트·thinking 텍스트 파싱, SSE 스트림 합산, JSON 편의 메서드
  - `gemini:test` Artisan 커맨드: 프롬프트 입력, --stream/--schema/--thinking 옵션
  - `.env`에 `GEMINI_API_KEY`, `config/services.php`에 gemini 설정 등록

### 주요 결정 사항
- GeminiService는 Laravel Http 클라이언트 사용 (Guzzle 직접 의존 없음)
- responseSchema는 선택적 적용 — null이면 responseMimeType 자체를 빼서 자유 텍스트 응답
- 스트리밍은 SSE 응답을 파싱하여 전체 텍스트로 합산 반환

---

### 작업 내용
- F-011 AI 자동 콘텐츠 생성 기능 구현
  - `slangs` 테이블에 `content_status` 컬럼 추가 (complete/pending/generated/approved)
  - `SlangAutoFillService`: Gemini responseSchema로 DB 구조에 맞는 콘텐츠 자동 생성, 카테고리 자동 매칭
  - `AutoFillSlangsCommand`: `slang:auto-fill` Artisan 커맨드, 5분 주기 cron 스케줄
  - 관리자 빠른 등록: 단어만 입력하면 pending 상태로 생성 (복수 단어 지원, 중복 체크)
  - 관리자 승인/반려 워크플로우: generated → approved(API 노출) 또는 → pending(재생성 대기)
  - API 필터링: `apiVisible()` 스코프로 complete/approved 상태만 노출 (모든 API 엔드포인트 일괄 적용)
  - Admin 뷰: 콘텐츠 상태 필터 탭, 상태 뱃지, 빠른 등록 모달, 승인/반려 버튼

### 주요 결정 사항
- content_status는 string(20) 컬럼으로 구현 (enum 대신, 마이그레이션 유연성)
- 기존 수동 등록 슬랭은 `complete` 상태로 기존 동작 영향 없음
- AI 생성 시 is_active=false로 설정하여 승인 전 노출 방지 이중 안전장치
- 반려 시 기존 AI 콘텐츠 초기화 후 pending으로 되돌려 자동 재생성 유도
- Cron 한 번에 최대 5건 처리로 API 부하 분산

---

## 2026-03-29

### 작업 내용
- 사용 상황 영어 번역 필드 추가
  - `slangs` 테이블에 `english_usage_context` 컬럼 추가 마이그레이션 생성
  - `Slang` 모델, `SlangService`, 생성/수정 FormRequest에 영어 사용 상황 필드 반영
  - 관리자 슬랭 폼에 `사용 상황 영어 번역` 입력란 추가
  - `SlangAutoFillService` 프롬프트/responseSchema를 확장하여 AI 생성 시 `english_usage_context`도 함께 저장
  - `SlangResource`와 검색 로직에 영어 사용 상황 필드 반영
  - Pest 테스트 2건 추가: AI 자동 생성 반영, API 응답 필드 노출

### 주요 결정 사항
- 기존 `usage_context`는 한글 설명으로 유지하고, 영어 번역은 별도 `english_usage_context` 컬럼으로 분리
- AI 자동 생성 시 영어 사용 상황은 별도 자유 생성값이 아니라 한글 사용 상황의 자연스러운 영어 번역으로 요청

---

### 작업 내용
- 관리자 API Playground 화면 추가
  - `ApiPlaygroundController`, `ExecuteApiPlaygroundRequest`, `ApiPlaygroundService` 생성
  - `/admin/api-playground` 화면과 `/admin/api-playground/request` 프록시 실행 라우트 추가
  - 엔드포인트 목록, path/query 파라미터 입력, cURL 예시, 상태 코드/헤더/본문/응답 시간 표시 UI 구현
  - 사이드바 메뉴에 API Playground 진입 링크 추가
  - Pest 테스트 3건 추가: 화면 접근, 프록시 요청, 필수 path 파라미터 검증
  - `docs/README.md`, `docs/app-api/SPECS.md`에 관리자 API Playground 내용 반영

### 주요 결정 사항
- 브라우저에 `APP_API_KEY`를 노출하지 않기 위해 관리자 화면에서는 서버 측 프록시가 현재 도메인의 `/api/v1/*`에 실제 HTTP 요청을 보내도록 구성
- 엔드포인트 정의를 `ApiPlaygroundService`에서 일원화하여 화면 렌더링, 요청 검증, cURL 미리보기 생성을 같은 메타데이터로 처리

---

### 작업 내용
- 슬랭 수정 화면 AI 부분 재생성 기능 추가
  - 설명, 사용 상황, 예문 섹션별 AI 재생성 엔드포인트 추가
  - 설명 재생성 시 영어/한글 설명을 함께 갱신하도록 구현
  - 사용 상황 재생성 시 한글/영어 사용 상황을 함께 갱신하도록 구현
  - 예문 재생성 시 기존 예문을 유지한 채 AI 예문 3개를 추가하도록 구현
  - 재생성 결과는 DB 즉시 저장 대신 수정 폼에만 반영 후 관리자 검토/저장 방식으로 처리
  - null `english_usage_context`로 인해 재생성 실패하던 케이스를 null-safe 처리로 보완
  - Pest 테스트 추가 및 `phpunit.xml`에 테스트용 `APP_KEY` 설정

### 주요 결정 사항
- AI 부분 재생성은 기존 저장 데이터를 덮어쓰지 않고, 수정 폼의 현재 입력값을 기준으로 결과만 반환해 검토 후 저장하도록 설계
- 기존 데이터 호환성을 위해 `english_usage_context`는 null이어도 재생성 프롬프트 빌드가 가능해야 함
