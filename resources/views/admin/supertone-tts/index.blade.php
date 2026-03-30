@extends('layouts.admin')

@section('title', 'Supertone TTS 테스트 - kslang Admin')

@php
    $languageOptions = [
        'ko' => '한국어 (ko)',
        'en' => '영어 (en)',
        'ja' => '일본어 (ja)',
        'bg' => '불가리아어 (bg)',
        'cs' => '체코어 (cs)',
        'da' => '덴마크어 (da)',
        'de' => '독일어 (de)',
        'el' => '그리스어 (el)',
        'es' => '스페인어 (es)',
        'et' => '에스토니아어 (et)',
        'fi' => '핀란드어 (fi)',
        'fr' => '프랑스어 (fr)',
        'hi' => '힌디어 (hi)',
        'hu' => '헝가리어 (hu)',
        'id' => '인도네시아어 (id)',
        'it' => '이탈리아어 (it)',
        'nl' => '네덜란드어 (nl)',
        'pl' => '폴란드어 (pl)',
        'pt' => '포르투갈어 (pt)',
        'ro' => '루마니아어 (ro)',
        'ru' => '러시아어 (ru)',
        'vi' => '베트남어 (vi)',
        'ar' => '아랍어 (ar)',
    ];

    $modelOptions = [
        'sona_speech_1' => 'sona_speech_1',
        'supertonic_api_1' => 'supertonic_api_1',
        'sona_speech_2' => 'sona_speech_2',
        'sona_speech_2_flash' => 'sona_speech_2_flash',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Supertone TTS 테스트</h2>
                <p class="mt-2 text-sm text-gray-500">
                    관리자 화면에서 텍스트를 바로 음성으로 변환하고, 생성된 결과를 mp3로 저장한 뒤 즉시 재생할 수 있습니다.
                </p>
            </div>

            <div class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">
                항상 mp3 형식으로 저장됩니다.
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Base URL</p>
                <p class="mt-2 font-mono text-sm text-indigo-600 break-all">{{ $configuration['base_url'] }}</p>
                <p class="mt-1 text-sm text-gray-500">Supertone API 실제 호출 주소</p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">기본 Voice ID</p>
                <p class="mt-2 font-mono text-sm text-gray-800 break-all">
                    {{ $configuration['configured_voice_id'] ?: '환경값 미설정' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">입력란을 비우면 이 값을 우선 사용합니다.</p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">API Key 상태</p>
                <p class="mt-2 text-lg font-semibold text-gray-800">
                    {{ $configuration['has_configured_api_key'] ? '환경값 감지됨' : '폼 직접 입력 필요' }}
                </p>
                <p class="mt-1 font-mono text-xs text-gray-500 break-all">
                    {{ $configuration['masked_api_key'] ?: '미설정' }}
                </p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">저장 위치</p>
                <p class="mt-2 font-mono text-sm text-gray-800 break-all">storage/app/public/audio/supertone-tts</p>
                <p class="mt-1 text-sm text-gray-500">최근 생성 결과는 아래 목록에서도 바로 재생할 수 있습니다.</p>
            </x-common.card>
        </div>

        <div id="form-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr),420px]">
            <x-common.card title="음성 생성 요청">
                <form id="supertone-tts-form" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">인증 / 타겟 설정</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    API Key는 서버에서만 사용되며 응답이나 저장 결과에 포함하지 않습니다.
                                </p>
                            </div>

                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                브라우저 직접 호출 아님
                            </span>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div>
                                <label for="api_key" class="block text-sm font-medium text-gray-700">
                                    Supertone API Key
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="api_key"
                                    name="api_key"
                                    type="password"
                                    autocomplete="off"
                                    value="{{ $defaultInput['api_key'] }}"
                                    placeholder="환경값이 없으면 여기에서 직접 입력"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $configuration['has_configured_api_key'] ? '비워두면 환경값 API Key를 사용합니다.' : '현재 환경값이 없어 직접 입력이 필요합니다.' }}
                                </p>
                                <p id="error-api_key" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>

                            <div>
                                <label for="voice_id" class="block text-sm font-medium text-gray-700">
                                    Voice ID
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="voice_id"
                                    name="voice_id"
                                    type="text"
                                    value="{{ $defaultInput['voice_id'] }}"
                                    placeholder="예: 4680c81c69d8490a044413"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">환경값이 있으면 기본값으로 채워지고, 필요하면 다른 Voice ID로 덮어쓸 수 있습니다.</p>
                                <p id="error-voice_id" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">텍스트 / 모델 옵션</h3>
                                <p class="mt-1 text-sm text-gray-500">Supertone 문서 기준 최대 300자까지 입력할 수 있습니다.</p>
                            </div>

                            <span id="text-counter" class="text-xs font-medium text-gray-400">0 / 300</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="text" class="block text-sm font-medium text-gray-700">
                                    테스트 텍스트
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="text"
                                    name="text"
                                    rows="5"
                                    maxlength="300"
                                    placeholder="예: 안녕하세요. 수퍼톤 TTS 테스트 페이지입니다."
                                    class="mt-2 min-h-32 w-full resize-y rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >{{ $defaultInput['text'] }}</textarea>
                                <p id="error-text" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-3">
                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700">
                                        언어 코드
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="language"
                                        name="language"
                                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        @foreach ($languageOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($defaultInput['language'] === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p id="error-language" class="mt-1 hidden text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label for="model" class="block text-sm font-medium text-gray-700">
                                        모델
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="model"
                                        name="model"
                                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        @foreach ($modelOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($defaultInput['model'] === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p id="error-model" class="mt-1 hidden text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label for="style" class="block text-sm font-medium text-gray-700">스타일</label>
                                    <input
                                        id="style"
                                        name="style"
                                        type="text"
                                        value="{{ $defaultInput['style'] }}"
                                        placeholder="예: neutral, happy"
                                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">비워두면 보이스 기본 스타일을 사용합니다.</p>
                                    <p id="error-style" class="mt-1 hidden text-xs text-red-600"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-5">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Voice Settings</h3>
                            <p class="mt-1 text-sm text-gray-500">속도, 음정, 억양을 미세하게 조정합니다.</p>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-3">
                            <div>
                                <label for="pitch_shift" class="block text-sm font-medium text-gray-700">Pitch Shift</label>
                                <input
                                    id="pitch_shift"
                                    name="pitch_shift"
                                    type="number"
                                    min="-24"
                                    max="24"
                                    step="1"
                                    value="{{ $defaultInput['pitch_shift'] }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">-24 ~ 24 반음 단위</p>
                                <p id="error-pitch_shift" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>

                            <div>
                                <label for="pitch_variance" class="block text-sm font-medium text-gray-700">Pitch Variance</label>
                                <input
                                    id="pitch_variance"
                                    name="pitch_variance"
                                    type="number"
                                    min="0"
                                    max="2"
                                    step="0.1"
                                    value="{{ $defaultInput['pitch_variance'] }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">0 ~ 2, 기본 1</p>
                                <p id="error-pitch_variance" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>

                            <div>
                                <label for="speed" class="block text-sm font-medium text-gray-700">Speed</label>
                                <input
                                    id="speed"
                                    name="speed"
                                    type="number"
                                    min="0.5"
                                    max="2"
                                    step="0.1"
                                    value="{{ $defaultInput['speed'] }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">0.5 ~ 2, 기본 1</p>
                                <p id="error-speed" class="mt-1 hidden text-xs text-red-600"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500">
                            저장된 mp3는 공개 스토리지 경로에 보관되며, 결과 카드와 최근 목록에서 바로 재생할 수 있습니다.
                        </p>

                        <x-common.button id="generate-button" type="submit">
                            음성 생성 및 저장
                        </x-common.button>
                    </div>
                </form>
            </x-common.card>

            <div class="space-y-6">
                <x-common.card title="생성 결과">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm text-gray-500">최근 요청의 저장 결과와 재생 상태를 확인합니다.</p>
                                <p id="result-meta" class="mt-1 text-xs text-gray-400">아직 생성한 결과가 없습니다.</p>
                            </div>

                            <span
                                id="result-status"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600"
                            >
                                대기
                            </span>
                        </div>

                        <div
                            id="result-empty"
                            class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500"
                        >
                            텍스트를 입력하고 생성 버튼을 누르면 저장된 mp3 정보와 재생 플레이어가 여기에 표시됩니다.
                        </div>

                        <div id="result-content" class="hidden space-y-4">
                            <audio id="result-audio" controls class="w-full"></audio>

                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">파일명</p>
                                        <p id="result-file-name" class="mt-1 text-sm font-medium text-gray-800 break-all"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">길이 / 크기</p>
                                        <p id="result-file-meta" class="mt-1 text-sm font-medium text-gray-800"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Voice / 모델</p>
                                        <p id="result-voice-meta" class="mt-1 text-sm font-medium text-gray-800 break-all"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">저장 시각</p>
                                        <p id="result-saved-at" class="mt-1 text-sm font-medium text-gray-800"></p>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">입력 텍스트</p>
                                    <p id="result-text" class="mt-2 whitespace-pre-wrap rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-6 text-gray-700"></p>
                                </div>

                                <div class="mt-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Voice Settings</p>
                                    <p id="result-voice-settings" class="mt-2 text-sm text-gray-700"></p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a
                                        id="result-open-link"
                                        href="#"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                    >
                                        새 탭에서 재생
                                    </a>
                                    <a
                                        id="result-download-link"
                                        href="#"
                                        download
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                    >
                                        mp3 다운로드
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-common.card>

                <x-common.card title="테스트 팁">
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                            <span>API Key와 Voice ID를 환경값으로 넣어두면 이후에는 텍스트만 바꿔가며 반복 테스트할 수 있습니다.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                            <span>생성 실패 시 Supertone API가 반환한 메시지를 그대로 보여주므로, 모델/언어 조합이 맞는지 바로 확인할 수 있습니다.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                            <span>최근 목록은 공개 스토리지의 메타데이터를 읽어오므로 DB나 마이그레이션 없이도 유지됩니다.</span>
                        </li>
                    </ul>
                </x-common.card>
            </div>
        </div>

        <x-common.card title="최근 저장된 mp3">
            <div
                id="recent-results-empty"
                class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500"
            >
                아직 저장된 결과가 없습니다.
            </div>

            <div id="recent-results-list" class="hidden grid gap-4 lg:grid-cols-2"></div>
        </x-common.card>
    </div>
@endsection

@push('scripts')
    <script>
        const generateUrl = @json(route('admin.supertone-tts.generate'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const recentResults = @json($recentResults);

        const elements = {
            form: document.getElementById('supertone-tts-form'),
            formError: document.getElementById('form-error'),
            generateButton: document.getElementById('generate-button'),
            textInput: document.getElementById('text'),
            textCounter: document.getElementById('text-counter'),
            resultMeta: document.getElementById('result-meta'),
            resultStatus: document.getElementById('result-status'),
            resultEmpty: document.getElementById('result-empty'),
            resultContent: document.getElementById('result-content'),
            resultAudio: document.getElementById('result-audio'),
            resultFileName: document.getElementById('result-file-name'),
            resultFileMeta: document.getElementById('result-file-meta'),
            resultVoiceMeta: document.getElementById('result-voice-meta'),
            resultSavedAt: document.getElementById('result-saved-at'),
            resultText: document.getElementById('result-text'),
            resultVoiceSettings: document.getElementById('result-voice-settings'),
            resultOpenLink: document.getElementById('result-open-link'),
            resultDownloadLink: document.getElementById('result-download-link'),
            recentResultsEmpty: document.getElementById('recent-results-empty'),
            recentResultsList: document.getElementById('recent-results-list'),
        };

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatSeconds(seconds) {
            if (seconds === null || seconds === undefined || Number.isNaN(Number(seconds))) {
                return '길이 정보 없음';
            }

            return `${Number(seconds).toFixed(2)}초`;
        }

        function formatVoiceSettings(voiceSettings = {}) {
            const entries = Object.entries(voiceSettings)
                .filter(([, value]) => value !== null && value !== undefined && value !== '');

            if (entries.length === 0) {
                return '기본값';
            }

            return entries
                .map(([key, value]) => `${key}: ${value}`)
                .join(' / ');
        }

        function setFormError(message = '', type = 'error') {
            const variants = {
                error: 'border border-red-200 bg-red-50 text-red-700',
                success: 'border border-emerald-200 bg-emerald-50 text-emerald-700',
            };

            elements.formError.className = `rounded-xl px-4 py-3 text-sm ${variants[type] ?? variants.error}`;

            if (!message) {
                elements.formError.classList.add('hidden');
                elements.formError.textContent = '';

                return;
            }

            elements.formError.textContent = message;
            elements.formError.classList.remove('hidden');
        }

        function clearValidationErrors() {
            document.querySelectorAll('[id^="error-"]').forEach((element) => {
                element.textContent = '';
                element.classList.add('hidden');
            });
        }

        function showValidationErrors(errors = {}) {
            Object.entries(errors).forEach(([key, messages]) => {
                const target = document.getElementById(`error-${key.replaceAll('.', '_')}`);

                if (!target) {
                    return;
                }

                target.textContent = messages[0];
                target.classList.remove('hidden');
            });
        }

        function setResultStatus(label, variant) {
            const variantClasses = {
                idle: 'bg-gray-100 text-gray-600',
                loading: 'bg-indigo-100 text-indigo-700',
                success: 'bg-emerald-100 text-emerald-700',
                error: 'bg-red-100 text-red-700',
            };

            elements.resultStatus.textContent = label;
            elements.resultStatus.className = `inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${variantClasses[variant] ?? variantClasses.idle}`;
        }

        function renderResult(result) {
            elements.resultMeta.textContent = `${result.request_duration_ms}ms · ${result.endpoint}`;
            setResultStatus('저장 완료', 'success');
            elements.resultEmpty.classList.add('hidden');
            elements.resultContent.classList.remove('hidden');
            elements.resultAudio.src = result.audio_url;
            elements.resultFileName.textContent = result.file_name;
            elements.resultFileMeta.textContent = `${formatSeconds(result.audio_length_seconds)} / ${result.file_size_human}`;
            elements.resultVoiceMeta.textContent = `${result.voice_id} · ${result.language} · ${result.model}${result.style ? ` · ${result.style}` : ''}`;
            elements.resultSavedAt.textContent = result.saved_at_display;
            elements.resultText.textContent = result.text;
            elements.resultVoiceSettings.textContent = formatVoiceSettings(result.voice_settings);
            elements.resultOpenLink.href = result.audio_url;
            elements.resultDownloadLink.href = result.audio_url;
        }

        function renderRecentResults(items = []) {
            if (!Array.isArray(items) || items.length === 0) {
                elements.recentResultsEmpty.classList.remove('hidden');
                elements.recentResultsList.classList.add('hidden');
                elements.recentResultsList.innerHTML = '';

                return;
            }

            elements.recentResultsEmpty.classList.add('hidden');
            elements.recentResultsList.classList.remove('hidden');
            elements.recentResultsList.innerHTML = items.map((item) => `
                <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900">${escapeHtml(item.text_preview || item.text)}</p>
                            <p class="mt-1 text-xs text-gray-500">${escapeHtml(item.saved_at_display)} · ${escapeHtml(item.file_name)}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                            ${escapeHtml(item.language)} / ${escapeHtml(item.model)}
                        </span>
                    </div>

                    <div class="mt-3 grid gap-2 text-xs text-gray-500 sm:grid-cols-2">
                        <p>Voice ID: <span class="font-medium text-gray-700">${escapeHtml(item.voice_id)}</span></p>
                        <p>길이: <span class="font-medium text-gray-700">${escapeHtml(formatSeconds(item.audio_length_seconds))}</span></p>
                        <p>크기: <span class="font-medium text-gray-700">${escapeHtml(item.file_size_human)}</span></p>
                        <p>설정: <span class="font-medium text-gray-700">${escapeHtml(formatVoiceSettings(item.voice_settings))}</span></p>
                    </div>

                    <audio controls class="mt-4 w-full" src="${escapeHtml(item.audio_url)}"></audio>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a
                            href="${escapeHtml(item.audio_url)}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            새 탭
                        </a>
                        <a
                            href="${escapeHtml(item.audio_url)}"
                            download
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-indigo-700"
                        >
                            다운로드
                        </a>
                    </div>
                </article>
            `).join('');
        }

        function updateTextCounter() {
            const length = elements.textInput.value.length;
            elements.textCounter.textContent = `${length} / 300`;
            elements.textCounter.className = `text-xs font-medium ${length > 300 ? 'text-red-600' : 'text-gray-400'}`;
        }

        function collectPayload() {
            return {
                api_key: document.getElementById('api_key').value,
                voice_id: document.getElementById('voice_id').value,
                text: document.getElementById('text').value,
                language: document.getElementById('language').value,
                style: document.getElementById('style').value,
                model: document.getElementById('model').value,
                pitch_shift: document.getElementById('pitch_shift').value,
                pitch_variance: document.getElementById('pitch_variance').value,
                speed: document.getElementById('speed').value,
            };
        }

        async function submitForm(event) {
            event.preventDefault();

            clearValidationErrors();
            setFormError('', 'error');
            setResultStatus('생성 중', 'loading');
            elements.resultMeta.textContent = 'Supertone API에 요청을 보내는 중입니다...';
            elements.generateButton.disabled = true;
            elements.generateButton.textContent = '생성 중...';

            try {
                const response = await fetch(generateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(collectPayload()),
                });

                const data = await response.json();

                if (response.status === 422) {
                    setFormError(data.message ?? '입력값을 확인해주세요.', 'error');
                    showValidationErrors(data.errors ?? {});
                    setResultStatus('검증 오류', 'error');
                    elements.resultMeta.textContent = '입력값 검증에 실패했습니다.';

                    return;
                }

                if (!response.ok) {
                    setFormError(data.message ?? '음성 생성에 실패했습니다.', 'error');
                    setResultStatus('요청 실패', 'error');
                    elements.resultMeta.textContent = `HTTP ${response.status} 오류`;

                    return;
                }

                renderResult(data.result);
                renderRecentResults(data.recent_results ?? []);
                setFormError(data.message ?? '음성 생성이 완료되었습니다.', 'success');
            } catch (error) {
                setFormError('네트워크 오류로 요청을 완료하지 못했습니다.', 'error');
                setResultStatus('네트워크 오류', 'error');
                elements.resultMeta.textContent = '서버 응답을 받지 못했습니다.';
            } finally {
                elements.generateButton.disabled = false;
                elements.generateButton.textContent = '음성 생성 및 저장';
            }
        }

        elements.form.addEventListener('submit', submitForm);
        elements.textInput.addEventListener('input', updateTextCounter);

        updateTextCounter();
        renderRecentResults(recentResults);
    </script>
@endpush
