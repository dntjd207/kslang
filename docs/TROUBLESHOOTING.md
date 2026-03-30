# 트러블슈팅

<!-- 템플릿
## 이슈 제목

### 증상
- 어떤 현상이 발생했는지

### 원인
- 문제의 근본 원인

### 해결 방법
- 구체적인 해결 과정

### 관련 파일
- 수정한 파일 경로
-->

## Windows/Herd 로컬 스토리지 404로 TTS 재생 실패

### 증상
- 관리자 Supertone TTS 화면에서 생성은 성공처럼 보이지만 오디오 플레이어 재생, 새 탭 열기, 다운로드가 모두 404가 발생함

### 원인
- `public/storage` 공개 링크가 로컬 환경에 없거나 정상적으로 노출되지 않아 `Storage::disk('public')->url()`이 가리키는 경로에 실제 파일 접근이 불가능했음

### 해결 방법
- Supertone TTS 결과 저장 디스크를 local `public`에서 `s3`로 전환
- S3 private bucket에서도 바로 재생할 수 있도록 temporary URL 옵션을 추가
- 관리자 화면과 최근 결과 목록이 항상 스토리지 디스크 기준 URL을 사용하도록 수정

### 관련 파일
- `app/Services/SupertoneTtsService.php`
- `resources/views/admin/supertone-tts/index.blade.php`
- `config/services.php`
- `.env.example`
