# F-012 Supertone TTS 테스트 관리

## 개요

관리자 화면에서 Supertone Text-to-Speech API를 직접 호출해 테스트 텍스트를 음성으로 변환하고, 결과를 mp3 파일로 저장한 뒤 바로 재생/다운로드할 수 있는 기능이다. DB 마이그레이션 없이 `public` 스토리지에 mp3와 메타데이터 JSON을 함께 저장한다.

## Routes

| Method | URI | Controller@Action | 설명 |
|--------|-----|-------------------|------|
| GET | /admin/supertone-tts | Admin\SupertoneTtsController@index | TTS 테스트 관리 페이지 |
| POST | /admin/supertone-tts/generate | Admin\SupertoneTtsController@generate | Supertone API 호출 + mp3 저장 |

## 관련 파일

- `app/Http/Controllers/Admin/SupertoneTtsController.php`
- `app/Http/Requests/Admin/GenerateSupertoneTtsRequest.php`
- `app/Services/SupertoneTtsService.php`
- `resources/views/admin/supertone-tts/index.blade.php`
- `resources/views/components/admin/sidebar.blade.php`
- `routes/web.php`
- `config/services.php`
- `.env.example`
- `tests/Feature/Admin/SupertoneTtsTest.php`

## 핵심 로직

- 관리자 페이지는 브라우저가 직접 Supertone API를 호출하지 않고, Laravel 서버가 `x-sup-api-key` 헤더를 붙여 프록시처럼 요청한다.
- 응답 오디오는 항상 `output_format=mp3`로 요청하고, `storage/app/public/audio/supertone-tts` 아래에 저장한다.
- 저장 시 mp3 파일과 함께 같은 basename의 JSON 메타데이터를 생성하여 최근 생성 목록을 DB 없이 유지한다.
- 환경값에 `SUPERTONE_API_KEY`, `SUPERTONE_VOICE_ID`가 없으면 관리자 폼에서 직접 입력한 값으로도 테스트할 수 있다.
- 최근 저장 목록은 메타데이터 JSON을 읽어와 텍스트 미리보기, 모델, 재생 시간, 파일 크기, 재생 링크를 렌더링한다.

## API 엔드포인트

- `POST https://supertoneapi.com/v1/text-to-speech/{voice_id}?output_format=mp3`

## 변경 이력

| 날짜 | 변경 내용 | 비고 |
|---|----|---|
| 2026-03-30 | 관리자 Supertone TTS 테스트 페이지 추가 | 서버 프록시, mp3 저장, 최근 결과 목록 |
