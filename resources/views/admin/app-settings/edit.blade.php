@extends('layouts.admin')

@section('title', '앱 설정')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-admin.alert />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">앱 설정</h1>

    <x-common.card title="앱 버전 관리" class="mb-6">
        <form method="POST" action="{{ route('admin.app-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="min_version" class="block text-sm font-medium text-gray-700 mb-1">
                    최소 지원 버전 <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="min_version"
                    name="min_version"
                    value="{{ old('min_version', $settings['min_version']) }}"
                    placeholder="예: 1.0.0"
                    required
                    class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $errors->has('min_version') ? 'border-red-500' : 'border-gray-300' }}"
                />
                <p class="mt-1 text-sm text-gray-500">이 버전 미만의 앱은 강제 업데이트됩니다.</p>
                @error('min_version')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="latest_version" class="block text-sm font-medium text-gray-700 mb-1">
                    최신 버전 <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="latest_version"
                    name="latest_version"
                    value="{{ old('latest_version', $settings['latest_version']) }}"
                    placeholder="예: 1.2.0"
                    required
                    class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $errors->has('latest_version') ? 'border-red-500' : 'border-gray-300' }}"
                />
                <p class="mt-1 text-sm text-gray-500">최신 앱 버전입니다. 이 버전보다 낮은 앱에 선택적 업데이트를 안내합니다.</p>
                @error('latest_version')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-200 my-6">

            <div class="mb-6">
                <label for="play_store_url" class="block text-sm font-medium text-gray-700 mb-1">
                    Play Store URL
                </label>
                <input
                    type="url"
                    id="play_store_url"
                    name="play_store_url"
                    value="{{ old('play_store_url', $settings['play_store_url']) }}"
                    placeholder="https://play.google.com/store/apps/..."
                    class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $errors->has('play_store_url') ? 'border-red-500' : 'border-gray-300' }}"
                />
                <p class="mt-1 text-sm text-gray-500">
                    Google Play Store 앱 페이지 URL입니다. 비워두면 기본 공식 링크({{ \App\Models\AppSetting::DEFAULT_PLAY_STORE_URL }})가 공개 사이트와 앱 버전 API에 사용됩니다.
                </p>
                @error('play_store_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <x-common.button variant="primary" type="submit">
                    저장
                </x-common.button>

                @if ($lastUpdated)
                    <span class="text-sm text-gray-400">
                        마지막 수정: {{ \Carbon\Carbon::parse($lastUpdated)->format('Y-m-d H:i:s') }}
                    </span>
                @endif
            </div>
        </form>
    </x-common.card>

    <div class="bg-blue-50 rounded-xl border border-blue-100 p-6">
        <h3 class="text-sm font-semibold text-blue-800 mb-3">버전 체크 로직 안내</h3>
        <p class="text-sm text-blue-700 mb-2">앱에서 다음과 같이 버전을 비교합니다:</p>
        <ul class="text-sm text-blue-700 space-y-2">
            <li class="flex items-start">
                <span class="mr-2">&bull;</span>
                <span><strong>앱 버전 &lt; 최소 지원 버전</strong> &rarr; 강제 업데이트 (앱 사용 차단)</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">&bull;</span>
                <span><strong>최소 지원 버전 &le; 앱 버전 &lt; 최신 버전</strong> &rarr; 선택적 업데이트 안내</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">&bull;</span>
                <span><strong>앱 버전 &ge; 최신 버전</strong> &rarr; 최신 상태 (알림 없음)</span>
            </li>
        </ul>
    </div>
</div>
@endsection
