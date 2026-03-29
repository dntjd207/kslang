@extends('layouts.admin')

@section('title', 'API 요청 테스트 - kslang Admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">API 요청 테스트</h2>
                <p class="mt-2 text-sm text-gray-500">
                    앱 API 요청을 한 페이지에서 정리해서 보고, 선택한 엔드포인트를 실제로 호출한 결과를 바로 확인할 수 있습니다.
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Base URL</p>
                <p class="mt-1 font-mono text-sm text-indigo-600 break-all">{{ $baseUrl }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">엔드포인트 수</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $endpointCount }}</p>
                <p class="mt-1 text-sm text-gray-500">현재 API 문서 기준 전체 GET 엔드포인트</p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">인증 방식</p>
                <p class="mt-2 text-lg font-semibold text-gray-800">X-API-Key</p>
                <p class="mt-1 text-sm text-gray-500">브라우저가 아닌 서버 프록시가 실제 헤더를 붙여 호출합니다.</p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">호출 방식</p>
                <p class="mt-2 text-lg font-semibold text-gray-800">실제 HTTP 요청</p>
                <p class="mt-1 text-sm text-gray-500">관리자 화면에서 선택한 엔드포인트를 현재 도메인으로 다시 요청합니다.</p>
            </x-common.card>

            <x-common.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Rate Limit</p>
                <p class="mt-2 text-lg font-semibold text-gray-800">분당 60회</p>
                <p class="mt-1 text-sm text-gray-500">테스트 요청도 실제 API 제한과 동일하게 영향을 받습니다.</p>
            </x-common.card>
        </div>

        <div class="grid gap-6 xl:grid-cols-[360px,minmax(0,1fr)]">
            <x-common.card title="엔드포인트 목록" :padding="false">
                <div class="divide-y divide-gray-100">
                    @foreach ($endpoints as $index => $endpoint)
                        <button
                            type="button"
                            data-endpoint-key="{{ $endpoint['key'] }}"
                            class="endpoint-button w-full px-5 py-4 text-left transition hover:bg-gray-50 {{ $index === 0 ? 'bg-indigo-50' : '' }}"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                            {{ $endpoint['method'] }}
                                        </span>
                                        <span class="text-[11px] font-medium text-gray-500">{{ $endpoint['group'] }}</span>
                                    </div>

                                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $endpoint['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-500">{{ $endpoint['description'] }}</p>
                                    <p class="mt-2 font-mono text-xs text-indigo-600 break-all">{{ $endpoint['uri'] }}</p>
                                </div>

                                <svg class="mt-1 h-5 w-5 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>
                    @endforeach
                </div>
            </x-common.card>

            <div class="space-y-6">
                <x-common.card title="요청 상세">
                    <form id="api-request-form" class="space-y-6">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    id="selected-method"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                ></span>
                                <span id="selected-uri" class="font-mono text-sm text-indigo-600 break-all"></span>
                            </div>

                            <p id="selected-description" class="mt-3 text-sm leading-6 text-gray-600"></p>

                            <div class="mt-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">요청 메모</p>
                                <ul id="selected-notes" class="mt-2 space-y-2 text-sm text-gray-600"></ul>
                            </div>
                        </div>

                        <div id="request-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                        <div class="grid gap-5 lg:grid-cols-2">
                            <div class="rounded-xl border border-gray-200 bg-white p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-800">Path Parameters</h3>
                                    <span id="path-param-count" class="text-xs text-gray-400"></span>
                                </div>

                                <p id="path-params-empty" class="mt-3 text-sm text-gray-500">필요한 경로 파라미터가 없습니다.</p>
                                <div id="path-params-fields" class="mt-4 space-y-4"></div>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-800">Query Parameters</h3>
                                    <span id="query-param-count" class="text-xs text-gray-400"></span>
                                </div>

                                <p id="query-params-empty" class="mt-3 text-sm text-gray-500">필요한 쿼리 파라미터가 없습니다.</p>
                                <div id="query-params-fields" class="mt-4 space-y-4"></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">실행될 요청</h3>
                                    <p class="mt-1 text-sm text-gray-500">아래 URL로 실제 HTTP 요청을 보내고 결과를 그대로 보여줍니다.</p>
                                </div>

                                <x-common.button id="send-request-button" type="submit">
                                    실제 요청 보내기
                                </x-common.button>
                            </div>

                            <div class="mt-5 space-y-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">URL</p>
                                    <div id="request-url" class="mt-2 rounded-lg border border-gray-200 bg-white px-3 py-2 font-mono text-xs text-gray-700 break-all"></div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">cURL 예시</p>
                                    <pre id="request-curl" class="mt-2 overflow-x-auto rounded-lg border border-gray-200 bg-slate-950 px-4 py-3 text-xs text-slate-100 whitespace-pre-wrap break-all"></pre>
                                </div>
                            </div>
                        </div>
                    </form>
                </x-common.card>

                <x-common.card title="응답 결과">
                    <div class="space-y-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm text-gray-500">실제 `/api/v1/*` 응답을 상태 코드, 헤더, 본문 그대로 확인할 수 있습니다.</p>
                                <p id="response-meta" class="mt-1 text-xs text-gray-400">아직 요청을 보내지 않았습니다.</p>
                            </div>

                            <span
                                id="response-status"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600"
                            >
                                대기
                            </span>
                        </div>

                        <div
                            id="response-empty"
                            class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500"
                        >
                            왼쪽에서 엔드포인트를 선택하고 실제 요청을 보내면 응답 결과가 여기에 표시됩니다.
                        </div>

                        <div id="response-content" class="hidden space-y-4">
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">실제 요청 정보</p>
                                    <pre id="executed-request" class="mt-3 overflow-x-auto rounded-lg bg-slate-950 px-4 py-3 text-xs text-slate-100 whitespace-pre-wrap break-all"></pre>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">응답 헤더</p>
                                    <pre id="response-headers" class="mt-3 overflow-x-auto rounded-lg bg-slate-950 px-4 py-3 text-xs text-slate-100 whitespace-pre-wrap break-all"></pre>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">응답 본문</p>
                                <pre id="response-body" class="mt-3 overflow-x-auto rounded-lg bg-slate-950 px-4 py-3 text-xs text-slate-100 whitespace-pre-wrap break-all"></pre>
                            </div>
                        </div>
                    </div>
                </x-common.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const endpoints = @json($endpoints);
        const executeUrl = @json(route('admin.api-playground.execute'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const endpointMap = Object.fromEntries(endpoints.map(endpoint => [endpoint.key, endpoint]));

        const elements = {
            form: document.getElementById('api-request-form'),
            buttons: document.querySelectorAll('.endpoint-button'),
            selectedMethod: document.getElementById('selected-method'),
            selectedUri: document.getElementById('selected-uri'),
            selectedDescription: document.getElementById('selected-description'),
            selectedNotes: document.getElementById('selected-notes'),
            pathParamCount: document.getElementById('path-param-count'),
            queryParamCount: document.getElementById('query-param-count'),
            pathParamsFields: document.getElementById('path-params-fields'),
            queryParamsFields: document.getElementById('query-params-fields'),
            pathParamsEmpty: document.getElementById('path-params-empty'),
            queryParamsEmpty: document.getElementById('query-params-empty'),
            requestUrl: document.getElementById('request-url'),
            requestCurl: document.getElementById('request-curl'),
            requestError: document.getElementById('request-error'),
            sendButton: document.getElementById('send-request-button'),
            responseMeta: document.getElementById('response-meta'),
            responseStatus: document.getElementById('response-status'),
            responseEmpty: document.getElementById('response-empty'),
            responseContent: document.getElementById('response-content'),
            executedRequest: document.getElementById('executed-request'),
            responseHeaders: document.getElementById('response-headers'),
            responseBody: document.getElementById('response-body'),
        };

        const state = {
            selectedKey: endpoints[0]?.key ?? null,
        };

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getSelectedEndpoint() {
            return endpointMap[state.selectedKey] ?? null;
        }

        function setRequestError(message = '') {
            if (!message) {
                elements.requestError.classList.add('hidden');
                elements.requestError.textContent = '';

                return;
            }

            elements.requestError.textContent = message;
            elements.requestError.classList.remove('hidden');
        }

        function clearValidationErrors() {
            document.querySelectorAll('[data-parameter-error]').forEach(element => {
                element.textContent = '';
                element.classList.add('hidden');
            });
        }

        function showValidationErrors(errors = {}) {
            Object.entries(errors).forEach(([key, messages]) => {
                const safeKey = key.replaceAll('.', '-').replaceAll('_', '-');
                const target = document.getElementById(`error-${safeKey}`);

                if (!target) {
                    return;
                }

                target.textContent = messages[0];
                target.classList.remove('hidden');
            });
        }

        function renderNotes(notes) {
            elements.selectedNotes.innerHTML = '';

            notes.forEach(note => {
                const item = document.createElement('li');
                item.className = 'flex items-start gap-2';
                item.innerHTML = `
                    <span class="mt-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                    <span>${escapeHtml(note)}</span>
                `;
                elements.selectedNotes.appendChild(item);
            });
        }

        function renderParameterFields(type, definitions) {
            const fieldsElement = type === 'path' ? elements.pathParamsFields : elements.queryParamsFields;
            const emptyElement = type === 'path' ? elements.pathParamsEmpty : elements.queryParamsEmpty;
            const countElement = type === 'path' ? elements.pathParamCount : elements.queryParamCount;

            countElement.textContent = `${definitions.length}개`;

            if (definitions.length === 0) {
                fieldsElement.innerHTML = '';
                emptyElement.classList.remove('hidden');

                return;
            }

            emptyElement.classList.add('hidden');

            fieldsElement.innerHTML = definitions.map(parameter => {
                const inputId = `${type}-${parameter.name}`;
                const errorId = `error-${type}-params-${parameter.name}`.replaceAll('_', '-');
                const inputType = parameter.type === 'number' ? 'number' : 'text';
                const requiredMark = parameter.required
                    ? '<span class="ml-1 text-red-500">*</span>'
                    : '';

                return `
                    <div>
                        <label for="${escapeHtml(inputId)}" class="block text-sm font-medium text-gray-700">
                            ${escapeHtml(parameter.label)}${requiredMark}
                        </label>
                        <input
                            id="${escapeHtml(inputId)}"
                            type="${escapeHtml(inputType)}"
                            value="${escapeHtml(parameter.default ?? '')}"
                            data-parameter-type="${escapeHtml(type)}"
                            data-parameter-name="${escapeHtml(parameter.name)}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">${escapeHtml(parameter.description)}</p>
                        <p id="${escapeHtml(errorId)}" data-parameter-error class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                `;
            }).join('');
        }

        function setButtonState() {
            elements.buttons.forEach(button => {
                const isActive = button.dataset.endpointKey === state.selectedKey;

                button.classList.toggle('bg-indigo-50', isActive);
                button.classList.toggle('ring-1', isActive);
                button.classList.toggle('ring-inset', isActive);
                button.classList.toggle('ring-indigo-100', isActive);
            });
        }

        function collectParameters(type) {
            const parameters = {};

            document.querySelectorAll(`[data-parameter-type="${type}"]`).forEach(input => {
                parameters[input.dataset.parameterName] = input.value;
            });

            return parameters;
        }

        function buildResolvedPath(endpoint, pathParams) {
            let path = endpoint.path;

            endpoint.path_params.forEach(parameter => {
                const value = pathParams[parameter.name];
                const replacement = value ? encodeURIComponent(value) : `{${parameter.name}}`;
                path = path.replace(`{${parameter.name}}`, replacement);
            });

            return path;
        }

        function buildQueryString(queryParams) {
            const searchParams = new URLSearchParams();

            Object.entries(queryParams).forEach(([name, value]) => {
                if (String(value).trim() !== '') {
                    searchParams.append(name, value);
                }
            });

            return searchParams.toString();
        }

        function updateRequestPreview() {
            const endpoint = getSelectedEndpoint();

            if (!endpoint) {
                return;
            }

            const pathParams = collectParameters('path');
            const queryParams = collectParameters('query');
            const resolvedPath = buildResolvedPath(endpoint, pathParams);
            const queryString = buildQueryString(queryParams);
            const requestUrl = `${window.location.origin}${resolvedPath}${queryString ? `?${queryString}` : ''}`;

            elements.requestUrl.textContent = requestUrl;
            elements.requestCurl.textContent = `curl -X ${endpoint.method} "${requestUrl}" -H "Accept: application/json" -H "X-API-Key: YOUR_API_KEY"`;
        }

        function resetResponse(emptyMessage = '왼쪽에서 엔드포인트를 선택하고 실제 요청을 보내면 응답 결과가 여기에 표시됩니다.') {
            elements.responseMeta.textContent = '아직 요청을 보내지 않았습니다.';
            elements.responseStatus.textContent = '대기';
            elements.responseStatus.className = 'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600';
            elements.responseEmpty.textContent = emptyMessage;
            elements.responseEmpty.classList.remove('hidden');
            elements.responseContent.classList.add('hidden');
            elements.executedRequest.textContent = '';
            elements.responseHeaders.textContent = '';
            elements.responseBody.textContent = '';
        }

        function statusBadgeClass(status) {
            if (status >= 200 && status < 300) {
                return 'inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700';
            }

            if (status >= 300 && status < 400) {
                return 'inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700';
            }

            if (status >= 400 && status < 500) {
                return 'inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700';
            }

            return 'inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700';
        }

        function formatOutput(value) {
            if (typeof value === 'string') {
                return value;
            }

            return JSON.stringify(value, null, 2);
        }

        function renderResponse(data) {
            elements.responseMeta.textContent = `${data.response.duration_ms}ms · ${data.request.url}`;
            elements.responseStatus.textContent = `${data.response.status} ${data.response.ok ? 'OK' : 'ERROR'}`;
            elements.responseStatus.className = statusBadgeClass(data.response.status);
            elements.responseEmpty.classList.add('hidden');
            elements.responseContent.classList.remove('hidden');
            elements.executedRequest.textContent = formatOutput(data.request);
            elements.responseHeaders.textContent = formatOutput(data.response.headers);
            elements.responseBody.textContent = formatOutput(data.response.body);
        }

        function renderProxyError(status, payload) {
            elements.responseMeta.textContent = `프록시 요청 실패 · HTTP ${status}`;
            elements.responseStatus.textContent = `${status} ERROR`;
            elements.responseStatus.className = statusBadgeClass(status);
            elements.responseEmpty.classList.add('hidden');
            elements.responseContent.classList.remove('hidden');
            elements.executedRequest.textContent = '';
            elements.responseHeaders.textContent = '';
            elements.responseBody.textContent = formatOutput(payload);
        }

        function selectEndpoint(key) {
            state.selectedKey = key;

            const endpoint = getSelectedEndpoint();

            if (!endpoint) {
                return;
            }

            setButtonState();
            clearValidationErrors();
            setRequestError('');

            elements.selectedMethod.textContent = endpoint.method;
            elements.selectedUri.textContent = endpoint.uri;
            elements.selectedDescription.textContent = endpoint.description;

            renderNotes(endpoint.notes);
            renderParameterFields('path', endpoint.path_params);
            renderParameterFields('query', endpoint.query_params);
            updateRequestPreview();
            resetResponse();
        }

        async function submitRequest(event) {
            event.preventDefault();

            const endpoint = getSelectedEndpoint();

            if (!endpoint) {
                return;
            }

            clearValidationErrors();
            setRequestError('');

            const payload = {
                endpoint_key: endpoint.key,
                path_params: collectParameters('path'),
                query_params: collectParameters('query'),
            };

            elements.sendButton.disabled = true;
            elements.sendButton.textContent = '요청 중...';
            elements.responseMeta.textContent = 'API 요청을 보내는 중입니다...';
            elements.responseStatus.textContent = 'LOADING';
            elements.responseStatus.className = 'inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700';

            try {
                const response = await fetch(executeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (response.status === 422) {
                    setRequestError(data.message ?? '입력값을 확인해주세요.');
                    showValidationErrors(data.errors ?? {});
                    resetResponse('입력값 검증 오류가 있어 요청을 보내지 못했습니다.');

                    return;
                }

                if (!response.ok) {
                    setRequestError(data.message ?? '요청 처리에 실패했습니다.');
                    renderProxyError(response.status, data);

                    return;
                }

                renderResponse(data);
            } catch (error) {
                setRequestError('네트워크 오류로 요청을 완료하지 못했습니다.');
                renderProxyError(0, {
                    message: '네트워크 오류가 발생했습니다.',
                });
            } finally {
                elements.sendButton.disabled = false;
                elements.sendButton.textContent = '실제 요청 보내기';
            }
        }

        elements.buttons.forEach(button => {
            button.addEventListener('click', () => selectEndpoint(button.dataset.endpointKey));
        });

        elements.form.addEventListener('input', updateRequestPreview);
        elements.form.addEventListener('submit', submitRequest);

        if (state.selectedKey) {
            selectEndpoint(state.selectedKey);
        }
    </script>
@endpush
