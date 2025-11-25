@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                앱 설정
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                버전 관리 및 점검 설정을 관리합니다.
            </p>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <!-- Heroicon name: check-circle -->
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm leading-5 font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.config.update') }}">
                @csrf

                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <!-- Version Code -->
                    <div class="sm:col-span-4">
                        <label for="version_code" class="block text-sm font-medium text-gray-700">
                            버전 코드
                        </label>
                        <div class="mt-1">
                            <input id="version_code" name="version_code" type="number" value="{{ $configs['version_code']->value ?? '' }}" required
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                        </div>
                    </div>

                    <!-- Force Update -->
                    <div class="sm:col-span-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="force_update" name="force_update" type="checkbox" {{ ($configs['force_update']->value ?? 'false') === 'true' ? 'checked' : '' }}
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="force_update" class="font-medium text-gray-700">강제 업데이트</label>
                                <p class="text-gray-500">사용자가 최신 버전으로 업데이트하도록 요구합니다.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Message -->
                    <div class="sm:col-span-6">
                        <label for="maintenance_message" class="block text-sm font-medium text-gray-700">
                            점검 메시지
                        </label>
                        <div class="mt-1">
                            <textarea id="maintenance_message" name="maintenance_message" rows="3"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">{{ $configs['maintenance_message']->value ?? '' }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            정상 운영 시 비워두세요.
                        </p>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-5">
                    <div class="flex justify-end">
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            설정 업데이트
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
