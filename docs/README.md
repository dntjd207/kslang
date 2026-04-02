# 프로젝트 기능 목록

| 기능 | 설명 | 문서 경로 |
|------|------|----------|
| F-000 프로젝트 초기 세팅 | Blade 레이아웃 분리, 공통 컴포넌트, 라우트/미들웨어/DB 스키마 구성 | `docs/initial-setup/SPECS.md` |
| F-001 관리자 로그인 | 세션 기반 관리자 인증 (로그인/로그아웃/인증 미들웨어) | `docs/admin-login/SPECS.md` |
| F-002 카테고리 관리 | 카테고리 CRUD + 드래그 앤 드롭 정렬 (모달, AJAX, SortableJS) | `docs/categories/SPECS.md` |
| F-003 욕/슬랭 관리 | 욕/슬랭 CRUD + 카테고리 연결 + 예문 관리 + 본문/예문 mp3 생성 및 재생 + Thread 콘텐츠 4포맷 생성/저장 + 드래그 정렬 + 활성 토글 + 검색/필터 + 공개 SEO 메타 필드 | `docs/slangs/SPECS.md` |
| F-006 개인정보처리방침 관리 | 위지윅(TinyMCE) 에디터로 개인정보처리방침 작성·수정, 공개 페이지/앱 웹뷰/API 제공, HTMLPurifier XSS 방지 | `docs/privacy-policy/SPECS.md` |
| F-007 이용약관 관리 | 위지윅(TinyMCE) 에디터로 이용약관 작성·수정, 공개 페이지/앱 웹뷰/API 제공, F-006과 인프라 공유 | `docs/terms/SPECS.md` |
| F-008 앱 버전 관리 | 앱 최소 지원 버전·최신 버전·Play Store URL 관리, 시맨틱 버저닝 검증, 앱 버전 체크 API | `docs/app-settings/SPECS.md` |
| F-009 랜딩 페이지 | 앱 소개 공개 랜딩 페이지, slang 미리보기 카드, Google Play 다운로드 CTA | `docs/landing/SPECS.md` |
| F-010 앱 API | 안드로이드 앱용 REST API 10개 엔드포인트 + 관리자용 API Playground(실시간 요청 테스트), X-API-Key 인증, API Resource 활용, 신규 단어 우선 노출(`is_new`) | `docs/app-api/SPECS.md` |
| F-011 AI 자동 콘텐츠 생성 | 빠른등록(단어만) / 상세등록(단어+설명) → Gemini AI 자동 콘텐츠 생성 (responseSchema), 관리자 승인 워크플로우, 승인 후 3일 신규 플래그 유지, cron 스케줄 | `docs/auto-fill/SPECS.md` |
| F-012 Supertone TTS 테스트 관리 | 관리자에서 Supertone TTS를 직접 호출해 mp3 저장, 재생, 다운로드, 최근 생성 결과 확인 | `docs/supertone-tts/SPECS.md` |
| F-013 SEO 블로그 & 공개 슬랭 허브 | 관리자 블로그 글 작성/자동 임시저장/발행 + 카테고리/태그 + AI 한국어 초안/영문 번역 + 공개 `/blog`, `/korean-slang` 허브 + CTA 클릭 추적 + sitemap/schema 연동 | `docs/blog-seo/SPECS.md` |
