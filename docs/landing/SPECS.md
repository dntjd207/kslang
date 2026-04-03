# 랜딩 페이지

## 개요

kslang 앱을 소개하는 공개 웹 랜딩 페이지(`/`). 영어로 작성되며, 앱의 핵심 기능을 소개하고 Google Play Store 다운로드를 유도한다. slangs 테이블에서 level 1~2의 활성 데이터를 최대 8개까지 미리보기 카드로 표시한다. 공개 CTA는 저장된 `play_store_url`이 비어 있어도 공식 Play Store URL을 fallback으로 사용한다.

## Routes

| Method | URI | Controller@Action | 미들웨어 | Name |
|--------|-----|-------------------|---------|------|
| GET | / | LandingController@index | 없음 (공개) | landing |

## 관련 파일

- `app/Http/Controllers/LandingController.php` — 컨트롤러
- `resources/views/public/landing.blade.php` — 메인 뷰
- `resources/views/partials/landing/hero.blade.php` — 히어로 섹션
- `resources/views/partials/landing/features.blade.php` — 앱 소개 섹션 (6개 카드: Trending Buzzwords, 4-Level, Audio, Context, Categories, Curated in Korea)
- `resources/views/partials/landing/curated.blade.php` — 한국 거주 관리자 검수 신뢰 어필 섹션
- `resources/views/partials/landing/preview.blade.php` — 미리보기 섹션
- `resources/views/partials/landing/download.blade.php` — 다운로드 CTA 섹션
- `resources/views/layouts/public.blade.php` — 공개 페이지 레이아웃
- `resources/views/components/public/navbar.blade.php` — 네비게이션
- `resources/views/components/public/footer.blade.php` — 푸터

## 핵심 로직

- slangs 테이블에서 `publicVisible()`, `level IN (1,2)`, `sort_order ASC`, `LIMIT 8` 조회
- app_settings 테이블에서 `play_store_url` 값 조회 (`AppSetting::getPlayStoreUrl()` 활용)
- `play_store_url`이 빈 문자열이거나 미설정이면 공식 Play Store URL(`https://play.google.com/store/apps/details?id=com.kslang.application`)을 fallback으로 사용
- 히어로/하단 다운로드 CTA는 `data-cta-track` 속성으로 클릭 추적 가능
- 미리보기 데이터 0건 시 안내 메시지 표시
- 미리보기 카드 클릭 시 공개 슬랭 상세(`/korean-slang/{public_slug}`)로 이동

## 페이지 구성

1. 히어로 섹션: 앱 이름, 슬로건(욕뿐 아니라 유행어/트렌드 포함 강조), Google Play 다운로드 버튼
2. 타겟 오디언스 섹션: K-Drama 팬, 여행자/거주자, 게이머/네티즌
3. 앱 소개 섹션: 6가지 기능 (Trending Buzzwords, 4-Level, Audio, Context, Categories, Curated in Korea) 그리드
4. 한국 거주 관리자 검수 섹션: Hand-Reviewed / Always Up-to-Date / Context That's Accurate + 신뢰 어필 카드
5. 레벨 시스템 섹션: 4단계 강도 스케일 (Mild → Moderate → Strong → Extreme)
6. 미리보기 섹션: slang 카드 (한국어, 발음, 레벨 뱃지, 설명)
7. 다운로드 CTA 섹션: 다운로드 유도 문구 + Google Play 버튼
8. 푸터: Privacy Policy, Terms 링크, 저작권

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | 랜딩 페이지 초기 구현 | F-009 |
| 2026-03-01 | 유행어/트렌드 표현 포함 강조 + 한국 거주 관리자 검수 신뢰 어필 섹션 추가, SEO 메타 태그 업데이트 | |
| 2026-04-01 | 미리보기 카드 공개 슬랭 상세 연결 + 공개 네비게이션 확장 | blog/slang 허브 연동 |
| 2026-04-02 | 기본 Play Store URL fallback + 랜딩 CTA 추적 연결 | 공식 앱 링크 고정 |
| 2026-04-03 | LCP 최적화: blob blur 지연 렌더링, 크리티컬 폰트 preload, stat 카드 backdrop-blur 제거 | 요소 렌더링 지연 2,560ms 개선 |
