# Web Design Guidelines

> 디자인 시스템 토큰 및 컴포넌트 스펙 레퍼런스

---

## Color Palette

### Primary

| 이름 | HEX | Tailwind | 용도 |
|------|-----|----------|------|
| Primary | `#4F46E5` | `bg-primary` | 주요 버튼, 링크, 강조 |
| Primary Hover | `#4338CA` | `hover:bg-primary-hover` | 호버 상태 |
| Primary Light | `#EEF2FF` | `bg-primary-light` | 연한 배경, 선택 하이라이트 |
| Primary 400 | `#818CF8` | `bg-primary-400` | 보조 강조 |
| Primary Dark | `#3730A3` | `bg-primary-dark` | 헤더, 푸터 |

### Secondary

| 이름 | HEX | Tailwind | 용도 |
|------|-----|----------|------|
| Secondary | `#06B6D4` | `bg-secondary` | 보조 버튼, 아이콘 |
| Secondary Hover | `#0891B2` | `hover:bg-secondary-hover` | 보조 호버 |
| Secondary Light | `#ECFEFF` | `bg-secondary-light` | 연한 배경 |

### Neutral

| HEX | Tailwind | 용도 |
|-----|----------|------|
| `#111827` | `text-gray-900` | 페이지 타이틀 |
| `#1F2937` | `text-gray-800` | 제목 |
| `#4B5563` | `text-gray-600` | 본문 텍스트 |
| `#9CA3AF` | `text-gray-400` | 보조 텍스트, 플레이스홀더 |
| `#E5E7EB` | `border-gray-200` | 테두리, 구분선 |
| `#F9FAFB` | `bg-gray-50` | 페이지 배경 |
| `#FFFFFF` | `bg-white` | 카드, 모달 배경 |

### Status

| 이름 | Tailwind 텍스트 | Tailwind 배경 | 용도 |
|------|----------------|---------------|------|
| Success | `text-emerald-500` | `bg-emerald-50` | 성공 |
| Warning | `text-amber-500` | `bg-amber-50` | 경고 |
| Error | `text-red-500` | `bg-red-50` | 에러, 삭제 |
| Info | `text-blue-500` | `bg-blue-50` | 정보 |

---

## Typography

### Font
- **Primary**: Pretendard (`font-sans`)
- **Fallback**: `-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`

### Headings

| Element | Desktop | Mobile | Weight | Tailwind |
|---------|---------|--------|--------|----------|
| H1 | 36px | 28px | 700 | `text-4xl md:text-3xl font-bold leading-tight mb-4` |
| H2 | 30px | 24px | 700 | `text-3xl md:text-2xl font-bold leading-snug mb-3` |
| H3 | 24px | 20px | 600 | `text-2xl md:text-xl font-semibold mb-2` |
| H4 | 20px | 18px | 600 | `text-xl md:text-lg font-semibold mb-2` |
| H5 | 18px | 16px | 600 | `text-lg md:text-base font-semibold mb-1` |
| H6 | 16px | 14px | 600 | `text-base md:text-sm font-semibold mb-1` |

### Body Text

| 이름 | Size | Tailwind | 용도 |
|------|------|----------|------|
| Lead | 18px | `text-lg leading-relaxed` | 소개문 |
| Base | 16px | `text-base leading-relaxed` | 기본 본문 |
| Small | 14px | `text-sm leading-normal` | 보조 텍스트 |
| XSmall | 12px | `text-xs leading-normal` | 캡션, 레이블 |

### Links
- Default: `text-primary font-medium hover:text-primary-hover transition-colors duration-150`
- Hover: `hover:underline underline-offset-2`
- Disabled: `text-gray-400 pointer-events-none`

---

## Button

### Sizes

| Size | Tailwind |
|------|----------|
| XSmall | `h-7 px-3 text-xs` |
| Small | `h-8 px-3.5 text-[13px]` |
| Medium | `h-9 px-4 text-sm` |
| Large | `h-10 px-5 text-[15px]` |
| XLarge | `h-12 px-7 text-base` |

### 공통 속성
- Border Radius: `rounded-lg`
- Font Weight: `font-medium`
- Transition: `transition-all duration-200 ease-out`
- Active: `active:scale-[0.97]`
- Focus: `focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2`

### Variants

| Variant | Tailwind |
|---------|----------|
| Primary | `bg-primary text-white hover:bg-primary-hover active:bg-primary-dark shadow-sm` |
| Secondary | `bg-secondary text-white hover:bg-secondary-hover shadow-sm` |
| Outline | `bg-transparent text-primary border border-primary/30 hover:bg-primary hover:text-white` |
| Ghost | `bg-transparent text-gray-600 hover:bg-gray-100` |
| Danger | `bg-red-500 text-white hover:bg-red-600 active:bg-red-700 shadow-sm` |
| Soft | `bg-primary-light text-primary hover:bg-primary-100` |

강조 순서: Primary > Secondary > Soft > Outline > Ghost

### Icon Buttons
- Square: `h-9 w-9 rounded-lg`
- Circle: `h-9 w-9 rounded-full`

---

## Form Elements

### 공통 속성
- Height: `h-9` (기본), `h-10` (대형)
- Padding: `px-3 py-2`
- Font: `text-sm`
- Border Radius: `rounded-lg`
- Border: `border border-gray-300`

### Input States

| 상태 | Tailwind |
|------|----------|
| Default | `border-gray-300` |
| Hover | `hover:border-gray-400` |
| Focus | `focus:border-primary focus:ring-2 focus:ring-primary/10` |
| Error | `border-red-500 focus:ring-red-500/10` |
| Disabled | `disabled:bg-gray-50 disabled:text-gray-400` |

### Label
- `text-sm font-medium text-gray-700 mb-1.5`

### Textarea
- 최소 높이: `min-h-24`, Resize: `resize-y`

### Checkbox / Radio
- Size: `h-4 w-4`, Border: `border-gray-300`, Focus: `focus:ring-2 focus:ring-primary/20`

---

## Card

### 기본 속성
- `bg-white border border-gray-200 rounded-xl shadow-sm`
- Padding: `p-5` ~ `p-6`
- Header: `px-5 py-3.5 border-b border-gray-100 bg-gray-50/50`
- Footer: `px-5 py-3 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-2`
- Hover: `hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 ease-out`

---

## Badge

### Sizes

| Size | Tailwind |
|------|----------|
| Small | `h-5 px-2 text-[11px]` |
| Medium | `h-6 px-2.5 text-xs` |
| Large | `h-7 px-3 text-[13px]` |

- `rounded-full font-medium`
- Soft 스타일 권장 (예: `bg-emerald-50 text-emerald-700`)

### Dot Badge (상태 인디케이터)
```html
<span class="inline-flex items-center gap-1.5 text-xs text-gray-600">
    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
    활성
</span>
```

---

## Dropdown

- `bg-white border border-gray-200 rounded-[10px] shadow-lg p-1 z-50`
- Menu Item: `px-2.5 py-2 text-sm rounded-md`, Hover: `bg-gray-50`, Active: `bg-primary-light text-primary`
- Divider: `my-1 border-t border-gray-100`

---

## Navigation

### Desktop
- Height: `h-14`, `bg-white border-b border-gray-200 sticky top-0 z-50 px-6`
- Menu: `text-sm text-gray-600 font-medium hover:text-gray-900`
- Active: `text-primary border-b-2 border-primary`

### Mobile
- Header: `h-12 px-4`
- Side Panel: `w-72`, 좌→우 슬라이드, `ease-out-expo duration-350`
- Overlay: `bg-black/40 backdrop-blur-[2px]`

### Sidebar
- Expanded: `w-60`, Collapsed: `w-16`
- Item: `px-3 py-2 text-sm rounded-lg`, Active: `bg-primary-light text-primary`

---

## Table

- Container: `overflow-hidden rounded-xl border border-gray-200`
- Header: `bg-gray-50`, `px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider`
- Body: `px-4 py-3 text-sm text-gray-700`, Divider: `divide-y divide-gray-100`
- Row Hover: `hover:bg-gray-50/50 transition-colors duration-100`

---

## Pagination

- Button: `h-8 w-8 text-[13px] rounded-lg gap-0.5`
- Active: `bg-primary text-white`
- Disabled: `text-gray-300 pointer-events-none`

---

## Modal

- Overlay: `bg-black/50 backdrop-blur-sm z-50`
- Window: `bg-white rounded-2xl shadow-2xl max-w-md`
- Header: `px-6 py-4 border-b border-gray-100`
- Body: `px-6 py-5 max-h-[65vh] overflow-y-auto`
- Footer: `px-6 py-3.5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-2`
- Body Scroll Lock: `document.body.style.overflow = modalOpen ? 'hidden' : ''`

---

## Toast

- 위치: 우상단 `top-4 right-4 z-[60]`
- Size: `min-w-80 max-w-[420px] px-4 py-3.5 rounded-[10px] shadow-lg`
- 스타일: 화이트 배경 + `border-l-4` 색상 액센트 (emerald/red/amber/blue)
- 표시 시간: 4초 (긴 메시지 6초)

---

## Spacing

| Tailwind | Size | 용도 |
|----------|------|------|
| `gap-1.5` | 6px | 레이블 ↔ 입력필드 |
| `gap-2` | 8px | 아이콘 ↔ 텍스트 |
| `gap-3` | 12px | 컴포넌트 내부 |
| `gap-4` / `p-4` | 16px | 기본 요소 간격 |
| `p-5` | 20px | 카드 내부 패딩 |
| `p-6` | 24px | 모달/섹션 패딩 |
| `mt-8` | 32px | 섹션 간 간격 |
| `mt-12` | 48px | 대형 섹션 간 간격 |
| `py-16` | 64px | 페이지 섹션 상하 여백 |

원칙: 관련 요소는 가깝게, 4px 그리드 기반, 수평 패딩 ≥ 수직 패딩

---

## Animation & Transition

### Duration

| Duration | 용도 | Tailwind |
|----------|------|----------|
| 100ms | 컬러 변경 | `duration-100` |
| 150ms | 호버, 포커스 | `duration-150` |
| 200ms | 드롭다운, 토글 | `duration-200` |
| 300ms | 오버레이 | `duration-300` |
| 350ms | 모달, 사이드 패널 | `duration-350` |

### Easing

| 이름 | 용도 | Tailwind |
|------|------|----------|
| Out | 열기 애니메이션 | `ease-out` |
| Out Expo | 모달/패널 열기 | `ease-out-expo` |
| In | 닫기/사라짐 | `ease-in` |

원칙: 열기는 느리게 마무리 (`ease-out`), 닫기는 빠르게 (`ease-in`), 열기 duration > 닫기 duration

### 마이크로 인터랙션

| 요소 | Tailwind |
|------|----------|
| 버튼 클릭 | `active:scale-[0.97]` |
| 카드 호버 | `hover:-translate-y-0.5 hover:shadow-md` |
| 링크 호버 | `transition-colors duration-150` |
| 토글 핸들 | `transition-transform duration-200 ease-out-expo` |

---

## Icons

- **라이브러리**: Font Awesome 6, 대안 Heroicons
- 버튼 내부: `text-sm`, 카드/섹션: `text-xl`, Hero: `text-3xl`+
- 아이콘 ↔ 텍스트 간격: `gap-2`

---

## Accessibility

- 색상만으로 의미 전달 금지 (아이콘/텍스트 병행)
- 대비: WCAG AA (최소 4.5:1)
- 포커스: `focus-visible:ring-2`
- 터치 영역: 최소 44px × 44px
- 아이콘 전용 버튼: `aria-label` 필수
- Modal: `role="dialog"`, `aria-modal="true"`, Escape 닫기
- Toggle: `role="switch"`, `:aria-checked`
- Dropdown: `aria-expanded`, `aria-haspopup="listbox"`
