# kslang 안드로이드 앱 구조 및 기능 명세서

## 1. 앱 개요 및 기술 스택

### 기본 정보
*   **앱 이름:** kslang
*   **플랫폼:** Android (Native)
*   **개발 언어:** Kotlin
*   **UI 프레임워크:** Jetpack Compose (Material 3)
*   **데이터 저장소:** Room Database (로컬 캐시), DataStore (설정)
*   **네트워크:** Retrofit2 (데이터 동기화), ExoPlayer (오디오 스트리밍)
*   **로그인:** 없음 (No Auth)

### 디자인 시스템 (Design System)
*   **Primary Color:** `#6B5FFF` (Soft Violet/Blue)
*   **Secondary Color:** `#FF6B6B` (Soft Red/Coral)
*   **Theme:** Modern Gradient Style, 깔끔한 Card UI (Light/Dark 지원)

### 아키텍처 (Architecture)
*   **MVVM + Repository:** UI, 비즈니스 로직, 데이터 계층 분리.
*   **Sync on Start:** 앱 실행 시 서버와 통신하여 최신 데이터를 로컬 DB에 동기화.
*   **On-demand Audio:** 오디오 파일은 미리 받지 않고 재생 시 실시간 다운로드/스트리밍.

---

## 2. 데이터 구조 (Data Structure)

### Table: `SlangWord` (단어 데이터)
*   서버로부터 받아와 로컬 `Room`에 저장하는 메인 데이터.

| 필드명 | 타입 | 설명 |
| :--- | :--- | :--- |
| `id` | Integer (PK) | 고유 식별자 |
| `word_korean` | String | 한국어 표기 (예: 헐) |
| `word_english` | String | 영어 발음/로마자 (예: Hul) |
| `level` | Integer | 1(Very Mild) ~ 5(Extreme) |
| `meaning` | String | 영어 뜻 |
| `etymology` | String | 어원 및 유래 |
| `example_kr` | String | 한국어 예문 |
| `example_en` | String | 영어 예문 |
| `audio_url` | String | 오디오 파일 다운로드/스트리밍 URL |
| `version` | Integer | 데이터 버전 관리용 |
| `is_bookmarked` | Boolean | 북마크 여부 (로컬 전용 필드, 서버 동기화 X) |
| `tags` | String | 검색용 태그 |

*(UserStats 테이블 삭제됨 - 통계 기능 제외)*

---

## 3. 주요 기능 및 화면 구성 (Screen Flow)

앱은 **Bottom Navigation Bar**를 통해 4개의 주요 탭으로 구성됩니다.
**(Home, Slang List, Levels, Quiz)**

### A. 홈 (Home)
*   **App Bar:** 로고.
*   **Hero Section:**
    *   "Learn Korean Slang Easily" 문구.
    *   **Start Learning** 버튼 -> Levels 탭으로 이동.
*   **Quick Links:**
    *   📂 **Browse Slang:** 전체 단어장(Slang List)으로 이동.
    *   💡 **Quiz Zone:** 퀴즈 탭으로 이동 (현재 준비 중 알림).

### B. 단어장 (Slang Dictionary / List)
*   **기능:** 로컬 DB에 저장된 전체 단어를 검색/필터링.
*   **UI 구성:**
    *   **Search Bar:** 검색.
    *   **Filter Chips:** Level, Alphabet, Popularity.
    *   **Word List:** 단어, 뜻, **재생 버튼**.
    *   **재생 동작:** 버튼 클릭 시 `audio_url`에서 오디오 스트리밍 재생.

### C. 레벨 (Slang Levels)
*   **기능:** 난이도별 학습 진입점 (1~5단계).
*   **구성:**
    *   **Level 1 (Very mild)**
    *   **Level 2 (Medium)**
    *   **Level 3 (Light/Intermediate)**
    *   **Level 4 (Strong)**
    *   **Level 5 (Extreme)**

### D. 퀴즈 존 (Quiz Zone) - *Update Pending*
*   **상태:** 메뉴는 존재하지만, 진입 시 또는 화면 내에 **"Coming Soon" (업데이트 예정)** 안내 표시.
*   **기능 (추후 구현):**
    *   객관식 퀴즈 (뜻 맞추기).
    *   현재 버전에서는 구현하지 않음.

### E. 단어 상세 (Word Details)
*   **UI:** Bottom Sheet 또는 별도 페이지.
*   **내용:**
    *   큰 한글 텍스트.
    *   **Audio:** 재생 버튼 (URL 스트리밍).
    *   **Translation & Examples:** 뜻과 예문 상세 표시.
    *   **Etymology:** 어원 설명.

*(Profile 탭 및 기능 삭제됨)*

---

## 4. 서버 및 업데이트 전략 (Modified)

### 데이터 동기화 (Data Sync)
1.  **앱 실행 시 (Splash/Loading):**
    *   서버의 `version.json`을 호출하여 현재 로컬 데이터 버전과 비교.
    *   **업데이트 필요 시:** 서버에서 전체 단어 데이터(JSON)를 받아와 로컬 Room DB를 갱신 (`Upsert` 또는 `Delete All & Insert`).
    *   **최신 상태:** 동기화 스킵하고 메인 화면 진입.
    *   **네트워크 오류 시:** 기존 로컬 데이터로 앱 실행 (Offline 모드 지원).

### 오디오 처리 (Audio Handling)
*   **전략:** 앱 용량을 줄이기 위해 오디오 파일은 앱 패키지에 포함하지 않음 (On-demand Download).
*   **동작:**
    1.  사용자가 재생 버튼(🔊) 클릭.
    2.  로컬 저장소(Cache Directory)에 해당 파일이 있는지 확인.
    3.  **파일 있음:** 로컬 파일 즉시 재생.
    4.  **파일 없음:** 서버 `audio_url`에서 다운로드 -> 로컬 저장 -> 재생.
*   **장점:** 데이터 사용량 절약 및 두 번째 재생부터 끊김 없는 경험 제공.
