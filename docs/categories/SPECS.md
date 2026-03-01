# 카테고리 관리 (F-002)

## 개요

욕/슬랭 데이터를 주제별로 분류하기 위한 카테고리 CRUD 및 정렬 기능. 관리자가 카테고리를 추가·수정·삭제하고, 드래그 앤 드롭으로 표시 순서를 변경할 수 있다.

## Routes

| Method | URI | Controller@Action | Name | 설명 |
|--------|-----|-------------------|------|------|
| GET | /admin/categories | CategoryController@index | admin.categories.index | 카테고리 목록 |
| POST | /admin/categories | CategoryController@store | admin.categories.store | 카테고리 생성 |
| PUT | /admin/categories/{category} | CategoryController@update | admin.categories.update | 카테고리 수정 |
| DELETE | /admin/categories/{category} | CategoryController@destroy | admin.categories.destroy | 카테고리 삭제 |
| POST | /admin/categories/reorder | CategoryController@reorder | admin.categories.reorder | 정렬 순서 저장 |

## 관련 파일

- `app/Models/Category.php` — Category 모델 (fillable, casts, slangs 관계)
- `app/Http/Controllers/Admin/CategoryController.php` — CRUD + reorder 컨트롤러
- `app/Http/Requests/Admin/StoreCategoryRequest.php` — 생성 유효성 검증
- `app/Http/Requests/Admin/UpdateCategoryRequest.php` — 수정 유효성 검증
- `app/Http/Requests/Admin/ReorderCategoryRequest.php` — 정렬 유효성 검증
- `resources/views/admin/categories/index.blade.php` — 카테고리 목록 + 모달 뷰
- `database/migrations/2026_02_28_000001_create_categories_table.php` — categories 테이블
- `database/migrations/2026_02_28_000003_create_category_slang_table.php` — 피벗 테이블

## 핵심 로직

- 카테고리 목록은 `sort_order` ASC 정렬, `withCount('slangs')`로 연결된 욕/슬랭 수 로드
- 생성 시 `sort_order`는 현재 최대값 + 1로 자동 할당
- 수정 시 `name` unique 검증에서 자기 자신 제외 (`Rule::unique()->ignore()`)
- 삭제 시 `category_slang` 피벗은 CASCADE 삭제, `slangs` 데이터는 유지
- 드래그 앤 드롭 정렬은 SortableJS(CDN) + `POST /admin/categories/reorder` AJAX
- 모든 CRUD는 AJAX(Fetch API)로 처리, JSON 응답 반환
- 생성/수정/삭제 모달 다이얼로그 사용 (별도 페이지 없음)

## API 엔드포인트

모든 엔드포인트는 `auth` 미들웨어 적용, JSON 응답.

### POST /admin/categories (생성)
- Request: `{ name: string, description?: string }`
- Response 201: `{ success: true, message: string, category: object }`
- Response 422: `{ errors: { name: string[], description: string[] } }`

### PUT /admin/categories/{category} (수정)
- Request: `{ name: string, description?: string }`
- Response 200: `{ success: true, message: string, category: object }`

### DELETE /admin/categories/{category} (삭제)
- Response 200: `{ success: true, message: string }`

### POST /admin/categories/reorder (정렬)
- Request: `{ orders: [{ id: int, sort_order: int }] }`
- Response 200: `{ success: true, message: string }`

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|---|---|---|
| 2026-02-28 | F-002 카테고리 관리 기능 구현 | 초기 구현 |
