# kslang 서버 요구사항 및 API 명세서 (Server & DB Requirements)

## 1. 서버 개요 (Overview)

kslang 앱은 **백엔드 API 서버**와 **데이터베이스(DB)** 를 통해 데이터를 관리하며, **오디오 파일은 서버의 파일 스토리지**에 저장하여 서빙합니다.

*   **서버 형태:** REST API를 제공하는 백엔드 서버 (Node.js, Python, Go 등)
*   **데이터베이스:** RDBMS (MySQL, PostgreSQL) 또는 NoSQL (MongoDB) 권장
*   **파일 스토리지:** 서버 로컬 디스크 또는 정적 파일 서버 (오디오 파일용)
*   **프로토콜:** HTTPS (필수)

---

## 2. 시스템 구조 (System Architecture)

### A. 디렉토리 구조 (File Storage)
오디오 파일은 서버의 특정 디렉토리(예: `uploads/audio/` 또는 `public/audio/`)에 저장되며, 웹 서버(Nginx, Apache 등)나 백엔드 프레임워크의 정적 파일 서빙 기능을 통해 접근합니다.

```text
/ (Server Root)
├── public/
│   └── audio/          # 오디오 파일 저장소 (URL로 접근 가능)
│       ├── slang_001.mp3
│       ├── slang_002.mp3
│       └── ...
└── src/                # 백엔드 소스 코드
```

### B. 데이터베이스 스키마 (Database Schema)
단어 데이터는 더 이상 JSON 파일이 아닌 DB 테이블에 저장됩니다.

**Table 1: `words` (단어 데이터)**

| Field Name | Type | Unique | Description |
| :--- | :--- | :--- | :--- |
| `id` | Integer / ObjectId | **PK** | 고유 ID |
| `word_korean` | String | Yes | 한국어 단어 (예: 헐) |
| `word_english` | String | - | 영어 표기 (예: Hul) |
| `level` | Integer | - | 난이도 (1~5) |
| `meaning` | String | - | 영어 뜻 (예: Oh my god) |
| `etymology` | Text | - | 어원/유래 설명 |
| `example_kr` | String | - | 한국어 예문 |
| `example_en` | String | - | 영어 예문 |
| `audio_filename` | String | - | 오디오 파일명 (예: slang_001.mp3) |
| `tags` | JSON / String | - | 태그 목록 |
| `created_at` | DateTime | - | 생성일 |
| `updated_at` | DateTime | - | 수정일 |

**Table 2: `app_config` (앱 설정 및 버전)**

| Field Name | Type | Description |
| :--- | :--- | :--- |
| `key` | String | 설정 키 (예: 'app_version') |
| `value` | String | 설정 값 (예: '15') |
| `description` | String | 설명 |

---

## 3. API 명세 (API Specifications)

앱과 서버는 JSON 포맷으로 통신합니다.

### A. 버전/상태 체크 (Check Server Status)
*   **Method:** `GET`
*   **Path:** `/api/v1/version`
*   **설명:** 서버의 데이터 버전을 확인합니다.
*   **Response:**
    ```json
    {
      "version_code": 15,
      "force_update": false,
      "maintenance_message": null
    }
    ```

### B. 단어 목록 조회 (Fetch Words)
*   **Method:** `GET`
*   **Path:** `/api/v1/words`
*   **설명:** DB에 저장된 모든 단어 목록을 반환합니다.
*   **Response:**
    ```json
    {
      "count": 2,
      "data": [
        {
          "id": 1001,
          "word_korean": "헐",
          "audio_filename": "slang_1001.mp3",
          "//": "..."
        }
      ]
    }
    ```

### C. 오디오 파일 스트리밍
*   **URL:** `https://{host}/audio/{audio_filename}`

---

## 4. 관리자 페이지 명세 (Admin Dashboard)

데이터 관리를 위해 웹 브라우저로 접속 가능한 **관리자(Admin) 페이지**가 필요합니다.

### A. 로그인 페이지 (Login)
*   **목적:** 관리자 외 접근 차단.
*   **기능:** ID/Password 입력, JWT 또는 세션 기반 로그인.

### B. 대시보드 (Dashboard - Main)
*   **목적:** 앱 운영 현황 한눈에 보기.
*   **구성 요소:**
    1.  **총 단어 수:** 현재 등록된 슬랭 단어 개수.
    2.  **최근 업데이트:** 마지막으로 단어가 추가/수정된 날짜.
    3.  **현재 앱 버전:** `version_code` 상태 표시.
    4.  **서버 상태:** 디스크 용량, DB 연결 상태 등 (선택 사항).

### C. 단어 관리 - 목록 (Word List)
*   **목적:** 등록된 단어들을 조회하고 관리.
*   **구성 요소:**
    1.  **데이터 테이블:** ID, 한국어, 영어, 레벨, 오디오 유무, 수정일 표시.
    2.  **검색/필터:** 단어 검색 창, 레벨별 필터링.
    3.  **액션 버튼:** 수정(Edit), 삭제(Delete), 신규 등록(Create) 버튼.
    4.  **페이징:** 한 페이지에 20~50개씩 표시.

### D. 단어 관리 - 등록/수정 (Word Editor)
*   **목적:** 새로운 단어를 추가하거나 기존 내용을 수정.
*   **입력 폼 (Form Fields):**
    1.  **기본 정보:** 한국어 단어, 영어 표기, 난이도(Level), 태그(Tags).
    2.  **상세 내용:** 뜻(Meaning), 어원(Etymology), 예문(KR/EN).
    3.  **오디오 파일 업로드 (핵심):**
        *   `input type="file"` 필드 제공.
        *   파일 선택 시 서버로 즉시 업로드되거나, 저장 버튼 클릭 시 폼 데이터와 함께 전송.
        *   업로드 성공 시 서버에서 생성된 파일명(예: `slang_1005.mp3`)이 DB `audio_filename` 필드에 자동 매핑되어야 함.
        *   기존 파일이 있는 경우 미리듣기(Play) 버튼 제공.

### E. 앱 설정 관리 (App Config)
*   **목적:** 앱의 버전 정보 및 공지사항 관리.
*   **구성 요소:**
    1.  **버전 코드 (Version Code):** 숫자 입력 필드. (데이터 변경 시 수동 또는 자동으로 1 증가시켜야 함).
    2.  **강제 업데이트 (Force Update):** On/Off 토글 스위치.
    3.  **점검 메시지 (Maintenance Message):** 텍스트 입력 (비어있으면 정상 운영).
