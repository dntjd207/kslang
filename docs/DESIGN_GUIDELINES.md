# KSlang Android Design System & UI Guidelines

이 문서는 **KSlang** 앱의 일관된 사용자 경험(UX)과 시각적 디자인(UI)을 위한 상세 가이드라인입니다.
개발 시 Jetpack Compose의 **Material 3** 테마를 기반으로 아래 커스텀 스타일을 적용합니다.

---

## 1. Design Philosophy (디자인 철학)

> **"Modern, Vibrant, yet Clean"**
>
> KSlang은 한국어 슬랭을 배우는 즐거움을 표현하기 위해 **밝고 현대적인 그라디언트**를 사용하지만, 학습에 방해되지 않도록 콘텐츠 영역은 **깔끔한 카드 UI**를 유지합니다.

*   **Shape:** 둥근 모서리 (Rounded Corners)를 적극 사용하여 친근한 느낌 강조.
*   **Depth:** 부드러운 그림자(Soft Shadow)를 사용하여 계층 구조 표현.
*   **Motion:** 화면 전환 및 터치 피드백에 부드러운 애니메이션 적용.

---

## 2. Color System (색상 시스템)

브랜드 아이덴티티를 나타내는 메인 컬러와 UI 요소에 사용되는 컬러 팔레트입니다.

### Brand Colors (Gradient Core)
앱의 로고, 주요 버튼, 헤더 배경에 사용되는 그라디언트 조합입니다.

| Color Name | Hex Code | Preview | Usage |
| :--- | :--- | :--- | :--- |
| **Primary Violet** | `#6B5FFF` | 🟣 | 메인 브랜드 컬러, 그라디언트 시작점 |
| **Secondary Coral** | `#FF6B6B` | 🔴 | 강조 컬러, 그라디언트 끝점 |
| **Gradient Flow** | Linear | 🟣 -> 🔴 | CTA 버튼, 로고 배경, Hero Section |

### UI Colors (Light / Dark)
가독성을 위한 배경 및 텍스트 컬러입니다.

| Type | Light Mode | Dark Mode | 설명 |
| :--- | :--- | :--- | :--- |
| **Background** | `#F5F5F5` | `#121212` | 앱 전체 배경색 |
| **Surface (Card)** | `#FFFFFF` | `#1E1E1E` | 카드, BottomSheet, 다이얼로그 배경 |
| **Text Primary** | `#212121` | `#EEEEEE` | 주요 텍스트 (단어, 제목) |
| **Text Secondary**| `#757575` | `#B0B0B0` | 부가 설명, 뜻, 예문 |
| **Divider** | `#E0E0E0` | `#333333` | 구분선 |

---

## 3. Typography (타이포그래피)

가독성이 좋은 Sans-Serif 계열(System Default / Pretendard 권장)을 사용합니다.

*   **Display Large (32sp, Bold):** 메인 단어 표시 (예: "헐")
*   **Title Medium (20sp, SemiBold):** 화면 타이틀, 카드 제목
*   **Body Large (16sp, Regular):** 단어 뜻, 예문
*   **Label Medium (12sp, Medium):** 칩(Chip), 버튼 텍스트

---

## 4. Component Library (컴포넌트 스타일)

### A. Buttons (CTA)
*   **Style:** Full Gradient Background (`#6B5FFF` to `#FF6B6B`)
*   **Shape:** Circle (Fully Rounded) or RoundedCorner(12dp)
*   **Effect:** 클릭 시 Ripple 효과 및 살짝 작아지는 Scale Animation.

### B. Cards (Slang Item)
*   **Background:** White (Light) / Dark Gray (Dark)
*   **Corner Radius:** `16dp`
*   **Elevation:** `4dp` (Soft Shadow)
*   **Layout:**
    *   좌측: 단어 텍스트
    *   우측: 재생 버튼 (Play Icon)
    *   하단: 태그 (Chip)

### C. Filter Chips
*   **State (Selected):** Primary Color Outline + Light Primary Background tint.
*   **State (Unselected):** Gray Outline + Transparent Background.
*   **Corner Radius:** `50%` (Capsule Shape).

### D. Bottom Navigation
*   **Container Color:** Surface Color.
*   **Active Icon:** Primary Color (`#6B5FFF`).
*   **Inactive Icon:** Gray (`#BDBDBD`).
*   **Indicator:** 선택된 아이템 주변에 은은한 배경색 없음 (깔끔한 아이콘 스타일).

---

## 5. Screen Layout Guidelines (화면별 가이드)

### 🏠 Home Screen (홈)
![Home Screen Wireframe Placeholder](https://via.placeholder.com/400x800?text=Home+Screen+Design)

1.  **Hero Section:** 상단 35% 영역. 그라디언트 배경. "Start Learning" 큰 버튼 배치.
2.  **Quick Links:** 2열 Grid 형태의 카드 배치 (Browse Slang / Quiz Zone). 아이콘을 크게 배치하여 터치 영역 확보.

### 📚 Slang Dictionary (단어장)
![Dictionary Screen Wireframe Placeholder](https://via.placeholder.com/400x800?text=Dictionary+List+Design)

1.  **Search Bar:** 상단 고정. 둥근 모서리(`24dp`). 그림자 적용.
2.  **Filter Row:** 가로 스크롤 가능한 칩 목록 (Level, Alphabet 등).
3.  **List:** 각 단어는 카드 형태로 나열. 카드 내부에 '재생 버튼' 필수 포함.

### 📶 Levels (난이도 선택)
![Levels Screen Wireframe Placeholder](https://via.placeholder.com/400x800?text=Level+Select+Design)

1.  **Layout:** 세로형 리스트.
2.  **Level Card:**
    *   왼쪽: 레벨 숫자 아이콘 (1~5) - 색상으로 난이도 구분 (Blue -> Red).
    *   중앙: 레벨 이름 (Very Mild ~ Extreme).
    *   오른쪽: 화살표 아이콘 (`>`).

### 📖 Word Details (상세 화면 - Bottom Sheet)
![Detail Screen Wireframe Placeholder](https://via.placeholder.com/400x800?text=Word+Detail+BottomSheet)

1.  **Type:** Modal Bottom Sheet (화면의 80% 높이까지 올라옴).
2.  **Header:** 상단 중앙에 'Handle bar' (작은 회색 바).
3.  **Content Flow:**
    *   **Main Word:** 가장 큰 폰트. 중앙 정렬.
    *   **Audio:** 단어 바로 옆 또는 아래에 큰 재생 버튼.
    *   **Meaning:** 구분선 아래에 영어 뜻 표시.
    *   **Examples:** 박스 형태로 감싸진 예문 영역 (한국어/영어 병기).
    *   **Etymology:** 하단에 작은 글씨로 어원 설명.

---

## 6. Iconography (아이콘)

*   **Style:** Filled(채워진) 또는 Rounded Outline 스타일 혼용.
*   **Home:** 🏠 Home / House
*   **List:** 📝 List / Book
*   **Levels:** 📶 Signal / Bar Chart
*   **Quiz:** ✅ Check / Quiz
*   **Audio:** ▶️ Play Circle / 🔊 Speaker

---

*이 문서는 개발 진행 상황에 따라 업데이트될 수 있습니다.*

