@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                대시보드
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                애플리케이션 통계 및 바로가기 개요
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Words Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 transition-shadow hover:shadow-md">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-brand-100 rounded-md p-3">
                        <!-- Heroicon name: book-open -->
                        <svg class="h-6 w-6 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                총 단어 수
                            </dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ number_format($wordCount) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.words.index') }}" class="font-medium text-brand-700 hover:text-brand-900 flex items-center transition-colors">
                        단어 관리하기 
                        <span aria-hidden="true" class="ml-1">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- App Version Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 transition-shadow hover:shadow-md">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <!-- Heroicon name: chip -->
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                현재 버전
                            </dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">
                                    v{{ $versionCode }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.config.index') }}" class="font-medium text-green-700 hover:text-green-900 flex items-center transition-colors">
                        설정 보기
                        <span aria-hidden="true" class="ml-1">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions (Future placeholder or simple text) -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 transition-shadow hover:shadow-md">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <!-- Heroicon name: lightning-bolt -->
                        <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                시스템 상태
                            </dt>
                            <dd>
                                <div class="flex items-center mt-1">
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        정상 운영 중
                                    </span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm text-gray-500">
                    서버가 정상적으로 작동 중입니다
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
