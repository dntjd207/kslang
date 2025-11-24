# kslang 안드로이드 앱 구조 및 기능 명세서

## 1. 앱 개요 및 기술 스택

### 기본 정보
*   **앱 이름:** kslang
*   **플랫폼:** Android (Native)
*   **개발 언어:** Kotlin
*   **UI 프레임워크:** Jetpack Compose (현대적인 UI 구축을 위함)
*   **데이터 저장소:** 로컬 중심 (Room Database + Pre-populated SQLite)
*   **로그인:** 없음 (No Auth)

### 아키텍처 (Architecture)
*   **MVVM (Model-View-ViewModel):** UI와 비즈니스 로직 분리.
*   **Repository Pattern:** 데이터 소스(로컬 DB vs 원격 서버) 추상화.
*   **Offline First:** 앱 설치 시 기본 데이터베이스(Slang.db)를 내장하여 배포. 인터넷 연결 없이도 기본 기능 100% 동작.

---

## 2. 데이터 구조 (Data Structure)

서버 없이 동작하기 위해 로컬 DB 테이블 설계를 명확히 합니다.

### Table: `SlangWord` (비속어/은어 데이터)
| 필드명 | 타입 | 설명 |
| :--- | :--- | :--- |
| `id` | Integer (PK) | 고유 식별자 |
| `word_korean` | String | 한국어 표기 (예: 대박) |
| `word_english` | String | 영어 발음 표기/로마자 (예: Daebak) |
| `pronunciation_ipa` | String | 발음 기호 (선택 사항) |
| `level` | Integer | 1(Mild) ~ 4(Taboo) 단계 |
| `meaning` | String | 영어 뜻 설명 |
| `etymology` | String | 어원 및 유래 (문화적 설명) |
| `example_sentence_kr` | String | 한국어 예문 |
| `example_sentence_en` | String | 영어 예문 번역 |
| `audio_file_name` | String | 로컬 raw 리소스 파일명 (예: `slang_001.mp3`) |
| `tags` | String | 검색용 태그 (예: "shock", "happy") |

---

## 3. 주요 기능 및 화면 구성 (Screen Flow)

### A. 스플래시 & 온보딩 (Splash & Onboarding)
*   **Splash:** kslang 로고 애니메이션.
*   **Onboarding:** 앱의 컨셉(4단계 레벨 시스템)을 간단히 설명하는 슬라이드. "No login required" 강조.

### B. 메인 화면 (Home / Level Select)
*   **구성:** 4개의 큰 카드 또는 버튼으로 구성된 대시보드.
*   **UI:**
    *   **Level 1:** 배경색 - 부드러운 톤, 아이콘 - 🍦 (Mild)
    *   **Level 2:** 배경색 - 중간 톤, 아이콘 - 🌶️ (Spicy)
    *   **Level 3:** 배경색 - 강한 톤, 아이콘 - 🔥 (Hot)
    *   **Level 4:** 배경색 - 경고색, 아이콘 - ☠️ (Danger)
*   **동작:** 레벨 카드를 터치하면 해당 레벨의 단어 리스트 화면으로 이동.

### C. 단어 리스트 화면 (Word List)
*   선택한 레벨에 해당하는 단어들을 리스트(LazyColumn)로 표시.
*   **아이템 구성:** 한국어 단어 + 영어 발음 표기.
*   **필터/정렬:** 가나다순, 인기순(추후 기능).

### D. 단어 상세 화면 (Detail View) - 학습의 핵심
*   **상단:** 크고 명확한 한국어 텍스트.
*   **오디오:** 🔊 버튼을 눌러 원어민 발음 재생 (Android MediaPlayer API 활용).
*   **발음 가이드:** 로마자 표기 및 발음 팁.
*   **뜻 (Meaning):** 직역(Literal)과 의역(Figurative)을 구분하여 설명.
*   **어원 (Origin):** 텍스트 박스로 유래 설명.
*   **예시 (Context):** 대화 형식의 예문 표시 (A: ..., B: ...).

---

## 4. 서버 및 콘텐츠 업데이트 전략 (Hybrid Approach)

사용자가 "서버가 필요할 것 같다"고 언급했으므로, **로그인 없는 콘텐츠 업데이트 서버**를 구성합니다.

### 1단계: 로컬 내장 (Initial Release)
*   앱 빌드 시 `assets/databases/slang.db`를 포함하여 배포.
*   장점: 서버 비용 0원, 즉시 실행 가능.

### 2단계: 원격 업데이트 (Content Update)
*   **목적:** 앱 업데이트(심사) 없이 새로운 욕/은어를 추가하기 위함.
*   **구성:**
    *   간단한 정적 호스팅 (AWS S3, Firebase Hosting, 또는 GitHub Pages)에 `version.json`과 `data_update.json` 업로드.
    *   앱 실행 시 `version.json`을 체크하여 로컬 DB 버전보다 높으면 `data_update.json`을 다운로드하여 로컬 Room DB에 `INSERT`.
*   **오디오 파일:** 용량이 작다면 JSON 내 Base64로 포함하거나, 필요시 다운로드(캐싱).

---

## 5. 디자인 및 UI 가이드라인 (Android)
*   **Theme:** 다크 모드(Dark Mode)를 기본으로 설정 (눈의 피로도 감소 및 "Underground" 느낌).
*   **Navigation:** Jetpack Compose Navigation 활용.
*   **Typography:** 한글 폰트 파일(`.ttf`)을 `res/font`에 포함하여 커스텀 폰트 적용.

