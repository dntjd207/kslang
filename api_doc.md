# KSlang API Documentation

한국어 욕/슬랭 사전 앱용 REST API 문서입니다.

## Base URL

```
https://{domain}/api/v1
```

## 인증

모든 API 요청에 `X-API-Key` 헤더가 필요합니다.

```
X-API-Key: {your-api-key}
```

| 헤더 | 필수 | 설명 |
|------|------|------|
| `X-API-Key` | O | 서버에서 발급된 API 키 |
| `Accept` | 권장 | `application/json` |

## Rate Limiting

- 분당 **60회** 요청 제한
- 초과 시 `429 Too Many Requests` 응답

---

## 에러 응답 형식

모든 에러는 동일한 JSON 형식으로 반환됩니다.

```json
{
  "error": "에러 유형",
  "message": "상세 메시지"
}
```

| HTTP Status | error | 설명 |
|-------------|-------|------|
| 401 | Unauthorized | API 키 누락 또는 불일치 |
| 404 | Not Found | 리소스가 존재하지 않음 |
| 429 | Too Many Requests | Rate Limit 초과 |
| 500 | Internal Server Error | 서버 내부 오류 |

---

## 엔드포인트 목록

| # | Method | URI | 설명 |
|---|--------|-----|------|
| 1 | GET | `/slangs` | 욕/슬랭 목록 |
| 2 | GET | `/slangs/search` | 욕/슬랭 검색 |
| 3 | GET | `/slangs/random` | 랜덤 슬랭 |
| 4 | GET | `/slangs/daily` | 오늘의 슬랭 |
| 5 | GET | `/slangs/{id}` | 욕/슬랭 상세 |
| 6 | GET | `/categories` | 카테고리 목록 |
| 7 | GET | `/categories/{id}` | 카테고리 상세 + 슬랭 |
| 8 | GET | `/app/version` | 앱 버전 정보 |
| 9 | GET | `/app/sync` | 데이터 동기화 정보 |
| 10 | GET | `/pages/{slug}` | 페이지 (약관/정책) |

---

## 1. 욕/슬랭 목록

```
GET /slangs
```

### Query Parameters

| 파라미터 | 타입 | 필수 | 기본값 | 설명 |
|----------|------|------|--------|------|
| `per_page` | int | X | 20 | 페이지당 항목 수 (최대 100) |
| `page` | int | X | 1 | 페이지 번호 |
| `level` | int | X | - | 레벨 필터 (1~4) |
| `category_id` | int | X | - | 카테고리 ID 필터 |

### Level 값

| 값 | 영문 라벨 | 설명 |
|----|-----------|------|
| 1 | Mild | 순한맛 |
| 2 | Moderate | 중간맛 |
| 3 | Strong | 매운맛 |
| 4 | Extreme | 극한맛 |

### Response

```json
{
  "data": [
    {
      "id": 1,
      "korean": "씨발",
      "pronunciation": "ssi-bal",
      "english_description": "The most common Korean swear word...",
      "korean_description": "가장 흔하게 사용되는 욕설...",
      "level": 4,
      "level_label": "Extreme",
      "usage_frequency": "매우 높음",
      "usage_context": "분노, 놀람, 강조 등 다양한 상황",
      "english_usage_context": "Used in situations of anger, surprise, or strong emphasis.",
      "audio_url": "https://{domain}/storage/audio/slangs/example.mp3",
      "categories": [
        {
          "id": 1,
          "name": "일반 욕설"
        }
      ],
      "examples": [
        {
          "korean_example": "씨발, 진짜 열받네.",
          "english_example": "F*ck, that really pisses me off."
        }
      ]
    }
  ],
  "links": {
    "first": "https://{domain}/api/v1/slangs?page=1",
    "last": "https://{domain}/api/v1/slangs?page=5",
    "prev": null,
    "next": "https://{domain}/api/v1/slangs?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 20,
    "to": 20,
    "total": 100,
    "path": "https://{domain}/api/v1/slangs"
  }
}
```

---

## 2. 욕/슬랭 검색

```
GET /slangs/search
```

### Query Parameters

| 파라미터 | 타입 | 필수 | 기본값 | 설명 |
|----------|------|------|--------|------|
| `q` | string | O | - | 검색어 (최소 2자) |
| `per_page` | int | X | 20 | 페이지당 항목 수 (최대 100) |
| `page` | int | X | 1 | 페이지 번호 |

### 검색 대상 필드

- `korean` (한국어)
- `pronunciation` (발음)
- `english_description` (영어 설명)
- `korean_description` (한국어 설명)

### 주의사항

- 검색어가 2자 미만이면 빈 결과를 반환합니다 (에러 아님).
- LIKE 검색이므로 부분 일치도 반환합니다.

### Response

슬랭 목록 API와 동일한 페이지네이션 형식입니다.

---

## 3. 랜덤 슬랭

```
GET /slangs/random
```

### Query Parameters

| 파라미터 | 타입 | 필수 | 기본값 | 설명 |
|----------|------|------|--------|------|
| `count` | int | X | 1 | 반환할 개수 (최대 10) |

### Response — count=1 (기본)

단일 슬랭 객체를 반환합니다.

```json
{
  "data": {
    "id": 7,
    "korean": "...",
    "pronunciation": "...",
    "english_description": "...",
    "korean_description": "...",
    "level": 2,
    "level_label": "Moderate",
    "usage_frequency": "...",
    "usage_context": "...",
    "english_usage_context": "...",
    "audio_url": null,
    "categories": [...],
    "examples": [...]
  }
}
```

### Response — count=2 이상

슬랭 배열을 반환합니다 (페이지네이션 없음).

```json
{
  "data": [
    { "id": 3, "korean": "...", ... },
    { "id": 12, "korean": "...", ... }
  ]
}
```

---

## 4. 오늘의 슬랭

```
GET /slangs/daily
```

파라미터 없음. 하루 동안 동일한 슬랭을 반환합니다 (서버 시간 기준, Asia/Seoul).

### Response

```json
{
  "data": {
    "id": 5,
    "korean": "...",
    "pronunciation": "...",
    "english_description": "...",
    "korean_description": "...",
    "level": 1,
    "level_label": "Mild",
    "usage_frequency": "...",
    "usage_context": "...",
    "english_usage_context": "...",
    "audio_url": "...",
    "categories": [...],
    "examples": [...]
  }
}
```

---

## 5. 욕/슬랭 상세

```
GET /slangs/{id}
```

### Path Parameters

| 파라미터 | 타입 | 설명 |
|----------|------|------|
| `id` | int | 슬랭 ID |

### Response

```json
{
  "data": {
    "id": 1,
    "korean": "...",
    "pronunciation": "...",
    "english_description": "...",
    "korean_description": "...",
    "level": 4,
    "level_label": "Extreme",
    "usage_frequency": "...",
    "usage_context": "...",
    "english_usage_context": "...",
    "audio_url": "https://{domain}/storage/audio/slangs/example.mp3",
    "categories": [
      { "id": 1, "name": "일반 욕설" },
      { "id": 3, "name": "감탄사" }
    ],
    "examples": [
      {
        "korean_example": "예문 한국어",
        "english_example": "Example English"
      }
    ]
  }
}
```

### 주의사항

- 비활성(`is_active: false`) 슬랭은 404를 반환합니다.
- 존재하지 않는 ID도 404를 반환합니다.

---

## 6. 카테고리 목록

```
GET /categories
```

파라미터 없음. 전체 카테고리를 정렬 순서대로 반환합니다.

### Response

```json
{
  "data": [
    {
      "id": 1,
      "name": "일반 욕설",
      "description": "일상에서 흔히 사용되는 욕설",
      "slang_count": 15
    },
    {
      "id": 2,
      "name": "감탄사",
      "description": "놀람이나 감정 표현에 사용",
      "slang_count": 8
    }
  ]
}
```

### 필드 설명

| 필드 | 타입 | 설명 |
|------|------|------|
| `id` | int | 카테고리 ID |
| `name` | string | 카테고리 이름 |
| `description` | string\|null | 카테고리 설명 |
| `slang_count` | int | 소속된 활성 슬랭 개수 |

---

## 7. 카테고리 상세 + 슬랭

```
GET /categories/{id}
```

### Path Parameters

| 파라미터 | 타입 | 설명 |
|----------|------|------|
| `id` | int | 카테고리 ID |

### Query Parameters

| 파라미터 | 타입 | 필수 | 기본값 | 설명 |
|----------|------|------|--------|------|
| `per_page` | int | X | 20 | 페이지당 슬랭 수 (최대 100) |
| `page` | int | X | 1 | 페이지 번호 |

### Response

```json
{
  "category": {
    "id": 1,
    "name": "일반 욕설",
    "description": "일상에서 흔히 사용되는 욕설",
    "slang_count": 15
  },
  "slangs": {
    "data": [
      {
        "id": 1,
        "korean": "...",
        "pronunciation": "...",
        ...
      }
    ],
    "links": { ... },
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 15,
      ...
    }
  }
}
```

---

## 8. 앱 버전 정보

```
GET /app/version
```

파라미터 없음. 앱 강제 업데이트 판단에 사용합니다.

### Response

```json
{
  "min_version": "1.0.0",
  "latest_version": "1.2.0",
  "play_store_url": "https://play.google.com/store/apps/details?id=com.example.kslang"
}
```

### 필드 설명

| 필드 | 타입 | 설명 |
|------|------|------|
| `min_version` | string\|null | 최소 지원 버전 (이 버전 미만은 강제 업데이트) |
| `latest_version` | string\|null | 최신 버전 (선택적 업데이트 안내용) |
| `play_store_url` | string\|null | Play Store 앱 페이지 URL |

### 앱에서의 활용 예시

```kotlin
// 강제 업데이트 판단 로직
val currentVersion = BuildConfig.VERSION_NAME
val minVersion = response.min_version

if (minVersion != null && isVersionLower(currentVersion, minVersion)) {
    // 강제 업데이트 다이얼로그 표시
    // play_store_url로 스토어 이동
}
```

---

## 9. 데이터 동기화 정보

```
GET /app/sync
```

파라미터 없음. 로컬 캐시 갱신 여부를 판단하는 데 사용합니다.

### Response

```json
{
  "slangs": {
    "total_count": 85,
    "last_updated_at": "2026-03-01 14:30:00"
  },
  "categories": {
    "total_count": 6,
    "last_updated_at": "2026-02-28 10:00:00"
  }
}
```

### 필드 설명

| 필드 | 타입 | 설명 |
|------|------|------|
| `slangs.total_count` | int | 활성 슬랭 총 개수 |
| `slangs.last_updated_at` | string\|null | 슬랭 마지막 수정 시각 (Y-m-d H:i:s) |
| `categories.total_count` | int | 카테고리 총 개수 |
| `categories.last_updated_at` | string\|null | 카테고리 마지막 수정 시각 |

### 앱에서의 활용 예시

```kotlin
// 앱 시작 시 sync API 호출
val syncInfo = api.getSync()

// 로컬에 저장된 last_updated_at과 비교
if (syncInfo.slangs.last_updated_at > localLastUpdated) {
    // 슬랭 데이터 새로 로드
}
```

---

## 10. 페이지 조회 (약관/정책)

```
GET /pages/{slug}
```

### Path Parameters

| 파라미터 | 타입 | 허용 값 | 설명 |
|----------|------|---------|------|
| `slug` | string | `privacy`, `terms` | 페이지 식별자 |

### Response

```json
{
  "data": {
    "title": "개인정보처리방침",
    "content": "<h2>제1조 (목적)</h2><p>이 개인정보처리방침은...</p>",
    "updated_at": "2026-02-28T15:30:00+09:00"
  }
}
```

### 필드 설명

| 필드 | 타입 | 설명 |
|------|------|------|
| `title` | string | 페이지 제목 |
| `content` | string | HTML 형식의 본문 (WebView로 렌더링) |
| `updated_at` | string | ISO 8601 형식 최종 수정일 |

### 앱에서의 활용

- `content`는 HTML이므로 Android의 **WebView**로 렌더링하세요.
- 서버에서 앱용 웹뷰 페이지도 제공합니다:
  - `https://{domain}/privacy?app=true`
  - `https://{domain}/terms?app=true`
- 웹뷰 URL을 직접 로드하거나, API에서 HTML을 받아 로컬 WebView에 표시하는 두 가지 방식 모두 가능합니다.

---

## 공통 데이터 모델

### Slang (슬랭)

| 필드 | 타입 | Nullable | 설명 |
|------|------|----------|------|
| `id` | int | X | 고유 ID |
| `korean` | string | X | 한국어 표현 |
| `pronunciation` | string | X | 발음 표기 |
| `english_description` | string | X | 영어 설명 |
| `korean_description` | string | X | 한국어 설명 |
| `level` | int | X | 수위 레벨 (1~4) |
| `level_label` | string | X | 레벨 영문 라벨 (Mild/Moderate/Strong/Extreme) |
| `usage_frequency` | string | X | 사용 빈도 |
| `usage_context` | string | X | 사용 맥락 |
| `english_usage_context` | string | X | 사용 맥락 영어 번역 |
| `audio_url` | string | O | 음성 파일 URL (없으면 null) |
| `categories` | array | X | 소속 카테고리 목록 |
| `examples` | array | X | 사용 예문 목록 |

### SlangCategory (슬랭의 카테고리)

| 필드 | 타입 | 설명 |
|------|------|------|
| `id` | int | 카테고리 ID |
| `name` | string | 카테고리 이름 |

### SlangExample (사용 예문)

| 필드 | 타입 | 설명 |
|------|------|------|
| `korean_example` | string | 한국어 예문 |
| `english_example` | string | 영어 번역 |

### Category (카테고리)

| 필드 | 타입 | Nullable | 설명 |
|------|------|----------|------|
| `id` | int | X | 고유 ID |
| `name` | string | X | 카테고리 이름 |
| `description` | string | O | 카테고리 설명 |
| `slang_count` | int | X | 활성 슬랭 개수 |

---

## 앱 구현 시 권장 사항

### 1. 앱 시작 시 호출 순서

```
1. GET /app/version   → 강제 업데이트 확인
2. GET /app/sync      → 데이터 갱신 필요 여부 확인
3. GET /categories    → 카테고리 목록 로드 (캐시와 비교 후)
4. GET /slangs/daily  → 홈 화면 오늘의 슬랭 표시
```

### 2. 오프라인 캐싱

- `/app/sync`의 `last_updated_at`을 로컬에 저장하여 변경 여부를 판단하세요.
- 슬랭 목록은 `per_page=100`으로 페이지네이션하여 로컬 DB에 저장할 수 있습니다.
- 카테고리는 전체 목록이 한 번에 반환되므로 그대로 캐싱하세요.

### 3. 음성 파일

- `audio_url`이 null이 아닌 경우에만 재생 버튼을 표시하세요.
- URL은 서버 도메인 기준 절대 경로입니다.
- 오프라인 지원 시 파일을 다운로드하여 로컬에 캐싱하세요.

### 4. 에러 처리

- 모든 에러는 `{ "error": "...", "message": "..." }` 형식입니다.
- 401: API 키 재확인 필요
- 404: 리소스 없음 (삭제되었거나 비활성화됨)
- 429: Rate Limit — 잠시 대기 후 재시도
- 500: 서버 오류 — 사용자에게 일시적 오류 안내

### 5. 페이지네이션

- `meta.last_page`로 마지막 페이지 여부를 확인하세요.
- `links.next`가 null이면 더 이상 데이터가 없습니다.
- 무한 스크롤 구현 시 `page` 파라미터를 증가시키며 호출하세요.
