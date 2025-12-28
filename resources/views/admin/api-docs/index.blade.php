@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl shadow-lg p-8 mb-8 text-white">
        <h1 class="text-3xl font-bold mb-2">API 문서</h1>
        <p class="text-indigo-100">앱과 서버 연동을 위한 API 엔드포인트 안내</p>
        <div class="mt-4 flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                Base URL
            </span>
            <code class="bg-white/10 px-4 py-2 rounded-lg font-mono text-sm">{{ $baseUrl }}</code>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="space-y-6">
        
        <!-- Version API -->
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-sm font-bold bg-green-100 text-green-800">
                        GET
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900">/version</h2>
                </div>
                <span class="text-sm text-gray-500">앱 버전 정보</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-2">설명</h3>
                <p class="text-gray-600 mb-4">현재 앱의 최신 버전 정보와 강제 업데이트 여부, 유지보수 메시지를 반환합니다.</p>
                
                <h3 class="text-sm font-medium text-gray-700 mb-2">요청 URL</h3>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}/version</code>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mb-2">응답 예시</h3>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-green-400 font-mono"><code>{
    "version_code": "1.0.0",
    "force_update": false,
    "maintenance_message": ""
}</code></pre>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mt-4 mb-2">응답 필드 설명</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">필드</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">타입</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">설명</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">version_code</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string</td>
                                <td class="px-4 py-2 text-sm text-gray-500">최신 앱 버전 코드</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">force_update</td>
                                <td class="px-4 py-2 text-sm text-gray-500">boolean</td>
                                <td class="px-4 py-2 text-sm text-gray-500">강제 업데이트 필요 여부</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">maintenance_message</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string</td>
                                <td class="px-4 py-2 text-sm text-gray-500">유지보수 메시지 (빈 문자열이면 정상)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Words API -->
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-sm font-bold bg-green-100 text-green-800">
                        GET
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900">/words</h2>
                </div>
                <span class="text-sm text-gray-500">단어 목록</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-2">설명</h3>
                <p class="text-gray-600 mb-4">등록된 모든 한국어 단어와 해당 예문 목록을 반환합니다.</p>
                
                <h3 class="text-sm font-medium text-gray-700 mb-2">요청 URL</h3>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}/words</code>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mb-2">응답 예시</h3>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-green-400 font-mono"><code>{
    "count": 2,
    "data": [
        {
            "id": 1,
            "word_korean": "대박",
            "word_english": "daebak",
            "level": 1,
            "meaning": "놀라운, 대단한",
            "etymology": "원래 도박에서 크게 따는 것을 의미...",
            "audio_filename": "slang_1234567890.mp3",
            "tags": "감탄사,일상",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z",
            "examples": [
                {
                    "id": 1,
                    "word_id": 1,
                    "example_kr": "와, 진짜 대박이다!",
                    "example_en": "Wow, that's amazing!",
                    "sort_order": 0,
                    "created_at": "2025-01-01T00:00:00.000000Z",
                    "updated_at": "2025-01-01T00:00:00.000000Z"
                },
                {
                    "id": 2,
                    "word_id": 1,
                    "example_kr": "이 음식 대박 맛있어!",
                    "example_en": "This food is so delicious!",
                    "sort_order": 1,
                    "created_at": "2025-01-01T00:00:00.000000Z",
                    "updated_at": "2025-01-01T00:00:00.000000Z"
                }
            ]
        }
    ]
}</code></pre>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mt-4 mb-2">Word 객체 필드 설명</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">필드</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">타입</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">설명</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">id</td>
                                <td class="px-4 py-2 text-sm text-gray-500">integer</td>
                                <td class="px-4 py-2 text-sm text-gray-500">단어 고유 ID</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">word_korean</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string</td>
                                <td class="px-4 py-2 text-sm text-gray-500">한국어 단어</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">word_english</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">영어 발음/로마자 표기</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">level</td>
                                <td class="px-4 py-2 text-sm text-gray-500">integer</td>
                                <td class="px-4 py-2 text-sm text-gray-500">난이도 레벨 (1-5)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">meaning</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">단어 뜻/의미</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">etymology</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">어원 설명</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">audio_filename</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">발음 오디오 파일명</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">tags</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">태그 (쉼표로 구분)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">examples</td>
                                <td class="px-4 py-2 text-sm text-gray-500">array</td>
                                <td class="px-4 py-2 text-sm text-gray-500">예문 목록</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mt-4 mb-2">Example 객체 필드 설명</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">필드</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">타입</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">설명</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">id</td>
                                <td class="px-4 py-2 text-sm text-gray-500">integer</td>
                                <td class="px-4 py-2 text-sm text-gray-500">예문 고유 ID</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">word_id</td>
                                <td class="px-4 py-2 text-sm text-gray-500">integer</td>
                                <td class="px-4 py-2 text-sm text-gray-500">연결된 단어 ID</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">example_kr</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string</td>
                                <td class="px-4 py-2 text-sm text-gray-500">한국어 예문</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">example_en</td>
                                <td class="px-4 py-2 text-sm text-gray-500">string|null</td>
                                <td class="px-4 py-2 text-sm text-gray-500">영어 번역</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">sort_order</td>
                                <td class="px-4 py-2 text-sm text-gray-500">integer</td>
                                <td class="px-4 py-2 text-sm text-gray-500">정렬 순서</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audio Files -->
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-sm font-bold bg-blue-100 text-blue-800">
                        FILE
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900">오디오 파일 접근</h2>
                </div>
                <span class="text-sm text-gray-500">발음 오디오</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-2">설명</h3>
                <p class="text-gray-600 mb-4">단어의 발음 오디오 파일에 접근하는 방법입니다.</p>
                
                <h3 class="text-sm font-medium text-gray-700 mb-2">오디오 파일 URL 형식</h3>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <code class="text-sm font-mono text-gray-800">{{ url('/storage/audio') }}/{audio_filename}</code>
                </div>

                <h3 class="text-sm font-medium text-gray-700 mb-2">예시</h3>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-green-400 font-mono"><code>// 단어 데이터에서 audio_filename이 "slang_1234567890.mp3"인 경우:
{{ url('/storage/audio') }}/slang_1234567890.mp3</code></pre>
                </div>
            </div>
        </div>

        <!-- Usage Tips -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">사용 팁</h3>
                    <div class="mt-2 text-sm text-amber-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>앱 시작 시 <code class="bg-amber-100 px-1 rounded">/version</code> API를 호출하여 업데이트 여부를 확인하세요.</li>
                            <li>단어 데이터는 로컬에 캐싱하여 오프라인에서도 사용할 수 있도록 구현하세요.</li>
                            <li>오디오 파일은 필요할 때만 다운로드하여 사용자 데이터 사용량을 절약하세요.</li>
                            <li>예문은 <code class="bg-amber-100 px-1 rounded">sort_order</code> 순서대로 정렬되어 반환됩니다.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

