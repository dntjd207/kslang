# 프로젝트 기능 목록

| 기능 | 설명 | 문서 경로 |
|------|------|----------|
| F-000 프로젝트 초기 세팅 | Blade 레이아웃 분리, 공통 컴포넌트, 라우트/미들웨어/DB 스키마 구성 | `docs/initial-setup/SPECS.md` |
| F-001 관리자 로그인 | 세션 기반 관리자 인증 (로그인/로그아웃/인증 미들웨어) | `docs/admin-login/SPECS.md` |
| F-002 카테고리 관리 | 카테고리 CRUD + 드래그 앤 드롭 정렬 (모달, AJAX, SortableJS) | `docs/categories/SPECS.md` |
| F-003 욕/슬랭 관리 | 욕/슬랭 CRUD + 카테고리 연결 + 예문 관리 + 음성 파일 + 드래그 정렬 + 활성 토글 + 검색/필터 | `docs/slangs/SPECS.md` |
| F-006 개인정보처리방침 관리 | 위지윅(TinyMCE) 에디터로 개인정보처리방침 작성·수정, 공개 페이지/앱 웹뷰/API 제공, HTMLPurifier XSS 방지 | `docs/privacy-policy/SPECS.md` |
| F-007 이용약관 관리 | 위지윅(TinyMCE) 에디터로 이용약관 작성·수정, 공개 페이지/앱 웹뷰/API 제공, F-006과 인프라 공유 | `docs/terms/SPECS.md` |
| F-008 앱 버전 관리 | 앱 최소 지원 버전·최신 버전·Play Store URL 관리, 시맨틱 버저닝 검증, 앱 버전 체크 API | `docs/app-settings/SPECS.md` |
| F-009 랜딩 페이지 | 앱 소개 공개 랜딩 페이지, slang 미리보기 카드, Google Play 다운로드 CTA | `docs/landing/SPECS.md` |
| F-010 앱 API | 안드로이드 앱용 REST API 7개 엔드포인트 (욕/슬랭 목록·상세·검색, 카테고리, 앱 버전, 개인정보처리방침, 이용약관), X-API-Key 인증, API Resource 활용 | `docs/app-api/SPECS.md` |
| F-011 AI 자동 콘텐츠 생성 | 단어만 등록 → Gemini AI 자동 콘텐츠 생성 (responseSchema), 관리자 승인 워크플로우, 5분 주기 cron | `docs/auto-fill/SPECS.md` |
