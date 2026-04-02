# SEO 블로그 & 공개 슬랭 허브 (F-013)

## 개요

영어권 검색 유입을 위한 공개 콘텐츠 허브 기능. 관리자는 한국어 기준으로 블로그 글을 작성·임시 저장·검수하고, AI로 영어 공개본을 생성한 뒤 발행할 수 있다. 블로그 글에는 카테고리/태그를 부여할 수 있고, draft 상태에서는 서버 자동 임시저장을 지원한다. 공개 웹에는 `/blog` 블로그 목록/상세와 `/korean-slang` 슬랭 허브/상세 페이지가 함께 구성되며, 블로그와 슬랭 상세는 내부 링크로 서로 연결된다.

## Routes

| Method | URI | Controller@Action | Name |
|--------|-----|-------------------|------|
| GET | /blog | BlogController@index | blog.index |
| GET | /blog/{blogPost:slug} | BlogController@show | blog.show |
| GET | /korean-slang | PublicSlangController@index | slangs.public.index |
| GET | /korean-slang/{slang:public_slug} | PublicSlangController@show | slangs.public.show |
| GET | /admin/blog-posts | Admin\BlogPostController@index | admin.blog-posts.index |
| GET | /admin/blog-posts/create | Admin\BlogPostController@create | admin.blog-posts.create |
| POST | /admin/blog-posts | Admin\BlogPostController@store | admin.blog-posts.store |
| GET | /admin/blog-posts/{blog_post}/edit | Admin\BlogPostController@edit | admin.blog-posts.edit |
| PUT | /admin/blog-posts/{blog_post} | Admin\BlogPostController@update | admin.blog-posts.update |
| DELETE | /admin/blog-posts/{blog_post} | Admin\BlogPostController@destroy | admin.blog-posts.destroy |
| POST | /admin/blog-posts/generate-draft | Admin\BlogPostController@generateDraft | admin.blog-posts.generate-draft |
| POST | /admin/blog-posts/translate | Admin\BlogPostController@translate | admin.blog-posts.translate |
| POST | /admin/blog-posts/autosave | Admin\BlogPostController@autosave | admin.blog-posts.autosave |

## 관련 파일

### 백엔드
- `app/Models/BlogPost.php` — 블로그 모델 (상태, 번역 상태, slug, 카테고리/태그 raw 필드, 공개 accessor, related slangs 관계)
- `app/Models/Slang.php` — 공개 SEO용 `public_slug`, `public_title_en`, `public_summary_en`, SEO 메타 필드 추가
- `app/Http/Controllers/Admin/BlogPostController.php` — 관리자 블로그 CRUD + AI 초안/번역 JSON 응답
- `app/Http/Controllers/BlogController.php` — 공개 블로그 목록/상세
- `app/Http/Controllers/PublicSlangController.php` — 공개 슬랭 목록/상세
- `app/Http/Controllers/SitemapController.php` — blog/slang 공개 URL sitemap 포함
- `app/Http/Requests/Admin/StoreBlogPostRequest.php`
- `app/Http/Requests/Admin/UpdateBlogPostRequest.php`
- `app/Http/Requests/Admin/AutoSaveBlogPostRequest.php`
- `app/Http/Requests/Admin/GenerateBlogPostDraftRequest.php`
- `app/Http/Requests/Admin/TranslateBlogPostRequest.php`
- `app/Services/BlogPostService.php` — 임시 저장/발행/자동 임시저장/slug 생성/카테고리·태그 정규화/번역 상태 판별/related slangs sync
- `app/Services/BlogPostAiService.php` — 한국어/영어 초안 생성 + 한국어 기준 영어 재번역
- `app/Services/GeminiService.php` — 모델 override 지원 (`translation_model`)
- `config/services.php` — `services.gemini.translation_model`

### 프론트엔드
- `resources/views/admin/blog-posts/index.blade.php` — 관리자 블로그 목록 (상태/번역 상태/카테고리/태그 필터)
- `resources/views/admin/blog-posts/create.blade.php`
- `resources/views/admin/blog-posts/edit.blade.php`
- `resources/views/admin/blog-posts/_form.blade.php` — 전략/카테고리/태그/한국어 원본/영어 공개본/SEO/관련 슬랭/상태 UI + autosave 상태 카드
- `resources/views/admin/blog-posts/_scripts.blade.php` — TinyMCE 2개 에디터 + AI draft/translate AJAX + SEO preview + tag chip preview + 자동 임시저장
- `resources/views/public/blog/index.blade.php` — 공개 블로그 목록 + category/tag 필터
- `resources/views/public/blog/show.blade.php` — 공개 블로그 상세 + category/tag badge
- `resources/views/public/slangs/index.blade.php`
- `resources/views/public/slangs/show.blade.php`
- `resources/views/components/admin/sidebar.blade.php` — Blog SEO 메뉴 추가
- `resources/views/components/public/navbar.blade.php` — Blog / Korean Slang 공개 메뉴 추가
- `resources/views/components/public/footer.blade.php` — Blog / Korean Slang / Privacy / Terms 링크 추가
- `resources/views/partials/landing/preview.blade.php` — 랜딩 미리보기 카드에서 공개 슬랭 상세로 링크

### 마이그레이션
- `database/migrations/2026_04_01_203730_create_blog_posts_table.php`
- `database/migrations/2026_04_01_203734_create_blog_post_slang_table.php`
- `database/migrations/2026_04_01_203734_add_public_seo_fields_to_slangs_table.php`

### 테스트
- `tests/Feature/Admin/BlogPostManagementTest.php`
- `tests/Feature/Admin/BlogPostAiActionsTest.php`
- `tests/Feature/BlogPublicPagesTest.php`
- `tests/Feature/PublicSlangPageTest.php`

## 핵심 로직

- 블로그 글은 `draft`, `published`, `archived` 상태를 가지며, `save_action`에 따라 임시 저장/발행/보관 처리
- 블로그 글은 `category_name`, `tag_names` raw 필드로 taxonomy를 저장하고, 관리자/공개 화면에서 filter/badge로 활용
- 영문 공개본 최신성은 `translation_status`(`none`, `synced`, `outdated`)로 별도 관리
- 한국어 원본이 바뀌고 영어가 같이 갱신되지 않으면 `translation_status=outdated`
- 발행 시에는 한국어 제목/본문과 최신 영어 제목/본문이 모두 있어야 함
- 블로그 slug는 직접 입력 가능하며, 비어 있으면 영어 제목 → 핵심 키워드 → 한국어 제목 순으로 자동 생성
- 새 글, draft, archived 글은 입력 후 잠시 멈추면 `/admin/blog-posts/autosave`로 자동 임시저장
- 발행된 글은 공개 중인 콘텐츠 보호를 위해 서버 자동 임시저장을 비활성화하고 수동 저장만 허용
- 블로그 본문과 영어 공개본은 TinyMCE 기반 HTML 에디터로 작성하며 `clean()`으로 저장 전 정화
- AI 초안 생성은 현재 폼 값을 기준으로 한국어/영어 초안과 SEO 메타를 함께 반환
- 영어 재번역은 한국어 원본을 기준으로 `gemini-3.1-flash-lite-preview` 모델로 생성하며, category/tag 입력도 프롬프트 맥락으로 함께 사용
- 공개 슬랭 상세는 기존 `slangs` 데이터를 재사용하며 `public_slug` 기반 URL을 사용
- 공개 blog/slang 페이지는 canonical, OG, Twitter 메타와 JSON-LD를 포함
- `sitemap.xml`에 blog 목록/상세와 공개 slang 목록/상세를 모두 포함

## API 엔드포인트

해당 기능은 공개 웹 + 관리자 웹 전용. 별도 앱 API 없음.

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-04-01 | F-013 SEO 블로그 & 공개 슬랭 허브 구현 | 관리자 블로그 CRUD, 임시 저장, AI 초안/번역, 공개 blog/slang 페이지, sitemap 연동 |
| 2026-04-02 | 카테고리/태그 + 자동 임시저장 + 관리자 UI polish 확장 | taxonomy raw 필드, autosave endpoint, public filter/badge |
