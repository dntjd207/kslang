# F-008 앱 버전 관리

## 개요

안드로이드 앱의 버전 정보와 Play Store URL을 관리하는 기능. 관리자가 최소 지원 버전(`min_version`), 최신 버전(`latest_version`), Play Store URL(`play_store_url`)을 설정하면, 앱에서 버전 체크 API를 호출하여 업데이트 필요 여부를 판단한다.

## Routes

| Method | URI | Controller@Action | 미들웨어 | 설명 |
|--------|-----|-------------------|---------|------|
| GET | /admin/app-settings | AppSettingController@edit | auth | 앱 설정 조회/수정 폼 |
| PUT | /admin/app-settings | AppSettingController@update | auth | 앱 설정 저장 |
| GET | /api/v1/app/version | Api\V1\AppController@version | api-key | 앱 버전 정보 JSON 응답 |

## 관련 파일

- `app/Models/AppSetting.php` — 키-값 설정 모델 (getValue, setValue, getMultiple)
- `app/Http/Controllers/Admin/AppSettingController.php` — 관리자 설정 조회/수정
- `app/Http/Controllers/Api/V1/AppController.php` — 앱 버전 체크 API
- `app/Http/Requests/Admin/UpdateAppSettingRequest.php` — 유효성 검증 (버전 형식 + 교차 검증)
- `resources/views/admin/app-settings/edit.blade.php` — 앱 설정 폼 뷰
- `database/migrations/2026_02_28_000006_create_app_settings_table.php` — 테이블 마이그레이션
- `database/seeders/DatabaseSeeder.php` — 초기 설정 데이터

## 핵심 로직

### 유효성 검증
- `min_version`, `latest_version`: 시맨틱 버저닝 형식 (`/^\d+\.\d+\.\d+$/`)
- `play_store_url`: nullable, URL 형식, 최대 500자
- 교차 검증: `version_compare()` 사용하여 `min_version ≤ latest_version` 확인

### 데이터 저장
- `AppSetting::setValue()` — `updateOrCreate` 방식으로 키-값 저장/업데이트
- `AppSetting::getMultiple()` — 여러 키를 한 번에 조회

### API 응답
- `GET /api/v1/app/version` → `{ "min_version": "x.y.z", "latest_version": "x.y.z" }`
- 설정값 없을 경우 기본값 `"1.0.0"` 반환
- `play_store_url`은 API 응답에 포함하지 않음

## API 엔드포인트

### GET /api/v1/app/version
- **인증**: X-API-Key 헤더
- **응답**: `{ "min_version": "1.0.0", "latest_version": "1.2.0" }`

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|------|----------|------|
| 2026-02-28 | F-008 앱 버전 관리 기능 구현 | 초기 구현 |
