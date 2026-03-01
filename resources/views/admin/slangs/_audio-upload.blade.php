@php
    $hasExistingAudio = isset($slang) && $slang->audio_file && $slang->hasAudioFile();
    $audioUrl = $hasExistingAudio ? $slang->audio_url : null;
    $slangId = isset($slang) ? $slang->id : null;
@endphp

<x-common.card title="음성 파일">
    <div id="audio-upload-section" data-slang-id="{{ $slangId }}">

        {{-- 상태 1: 파일 없음 (드래그 앤 드롭 영역) --}}
        <div id="audio-dropzone" class="{{ $hasExistingAudio ? 'hidden' : '' }}">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer
                        hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors duration-200"
                 id="audio-drop-area">
                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
                <p class="text-sm text-gray-600 mb-1">mp3 파일을 여기에 드래그하거나</p>
                <button type="button" id="audio-select-btn"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline">
                    파일 선택
                </button>
                <p class="text-sm text-gray-600 mt-1">버튼을 클릭하세요</p>
                <p class="text-xs text-gray-400 mt-2">최대 5MB, mp3 형식만</p>
            </div>
        </div>

        {{-- 상태 2: 새 파일 선택됨 (미리듣기) --}}
        <div id="audio-preview" class="hidden">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-5 w-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span id="audio-filename" class="text-sm font-medium text-gray-700 truncate"></span>
                <span id="audio-filesize" class="text-xs text-gray-500 shrink-0"></span>
            </div>
            <audio id="audio-preview-player" controls class="w-full mb-3" preload="metadata"></audio>
            <div class="flex gap-2">
                <button type="button" id="audio-change-btn"
                        class="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition">
                    다른 파일 선택
                </button>
                <button type="button" id="audio-cancel-btn"
                        class="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition">
                    취소
                </button>
            </div>
        </div>

        {{-- 상태 3: 기존 파일 있음 (수정 시) --}}
        <div id="audio-existing" class="{{ $hasExistingAudio ? '' : 'hidden' }}" data-has-file="{{ $hasExistingAudio ? 'true' : 'false' }}">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">음성 파일 등록됨</span>
            </div>
            @if($hasExistingAudio)
                <audio id="audio-existing-player" controls class="w-full mb-3" preload="metadata">
                    <source src="{{ $audioUrl }}" type="audio/mpeg">
                </audio>
            @endif
            <div class="flex gap-2">
                <button type="button" id="audio-replace-btn"
                        class="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition">
                    교체
                </button>
                <button type="button" id="audio-delete-btn"
                        class="text-sm px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition">
                    삭제
                </button>
            </div>
        </div>

        {{-- 숨겨진 파일 입력 --}}
        <input type="file" id="audio-file-input" name="audio_file" accept=".mp3,audio/mpeg" class="hidden">

        {{-- 에러 메시지 영역 --}}
        <div id="audio-error" class="hidden mt-2">
            <p class="text-sm text-red-500" id="audio-error-message"></p>
        </div>
        @error('audio_file')
            <div class="mt-2">
                <p class="text-sm text-red-500">{{ $message }}</p>
            </div>
        @enderror
    </div>
</x-common.card>

{{-- 음성 파일 삭제 확인 모달 --}}
@if($hasExistingAudio)
<div id="audio-delete-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" id="audio-delete-modal-overlay"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full relative z-10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">음성 파일 삭제</h3>
                <button type="button" id="audio-delete-modal-close"
                        class="h-8 w-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-1">이 음성 파일을 삭제하시겠습니까?</p>
            <p class="text-sm text-gray-500 mb-4">삭제된 파일은 복구할 수 없습니다.</p>
            <div class="flex justify-end gap-2">
                <button type="button" id="audio-delete-modal-cancel"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    취소
                </button>
                <button type="button" id="audio-delete-confirm"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    삭제
                </button>
            </div>
        </div>
    </div>
</div>
@endif
