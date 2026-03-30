@php
    $exampleAudioFile = old("examples.{$index}.audio_file", $example['audio_file'] ?? '');
    $exampleAudioDisk = old("examples.{$index}.audio_disk", $example['audio_disk'] ?? '');
    $exampleAudioUrl = $example['audio_url']
        ?? (filled($exampleAudioFile) ? app(\App\Services\AudioFileService::class)->getUrl($exampleAudioFile, $exampleAudioDisk) : null);
    $hasExampleAudio = filled($exampleAudioFile) && filled($exampleAudioUrl);
@endphp

<div class="example-row rounded-lg border border-gray-200 bg-gray-50 p-4" data-index="{{ $index }}">
    @if (!empty($example['id']))
        <input type="hidden" name="examples[{{ $index }}][id]" value="{{ $example['id'] }}">
    @endif

    <input type="hidden" name="examples[{{ $index }}][audio_file]" value="{{ $exampleAudioFile }}">
    <input type="hidden" name="examples[{{ $index }}][audio_disk]" value="{{ $exampleAudioDisk }}">

    <div class="flex flex-col gap-3 xl:flex-row xl:items-start">
        <span class="drag-handle-example cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 shrink-0 xl:mt-7">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
            </svg>
        </span>

        <div class="min-w-0 flex-1 space-y-3">
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1fr),minmax(0,1fr),auto]">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">한국어 예문</label>
                    <input type="text"
                           name="examples[{{ $index }}][korean_example]"
                           value="{{ old("examples.{$index}.korean_example", $example['korean_example'] ?? '') }}"
                           placeholder="예: 씨발, 또 늦었어!"
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error("examples.{$index}.korean_example") border-red-500 @else border-gray-300 @enderror"
                    />
                    @error("examples.{$index}.korean_example")
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">영어 번역</label>
                    <input type="text"
                           name="examples[{{ $index }}][english_example]"
                           value="{{ old("examples.{$index}.english_example", $example['english_example'] ?? '') }}"
                           placeholder="예: F**k, I'm late again!"
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error("examples.{$index}.english_example") border-red-500 @else border-gray-300 @enderror"
                    />
                    @error("examples.{$index}.english_example")
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="xl:pt-7">
                    <button type="button" class="remove-example inline-flex items-center gap-1.5 rounded-lg p-2 text-red-400 transition hover:bg-red-50 hover:text-red-600" title="예문 삭제">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-sm font-medium xl:hidden">삭제</span>
                    </button>
                </div>
            </div>

            @if (isset($slang))
                <div class="rounded-lg border border-gray-200 bg-white p-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">예문 mp3</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ !empty($example['id']) ? '생성하면 즉시 저장됩니다.' : '새 예문은 생성 후 전체 저장 시 DB에 반영됩니다.' }}
                            </p>
                        </div>
                        <x-common.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            class="generate-example-audio"
                            data-loading-text="예문 mp3 생성 중..."
                        >
                            예문 mp3 생성
                        </x-common.button>
                    </div>

                    <p class="example-audio-empty mt-3 text-xs text-gray-400 {{ $hasExampleAudio ? 'hidden' : '' }}">
                        아직 생성된 예문 mp3가 없습니다.
                    </p>

                    <div class="example-audio-player-wrapper mt-3 {{ $hasExampleAudio ? '' : 'hidden' }}">
                        <audio
                            class="example-audio-player w-full"
                            controls
                            preload="metadata"
                            src="{{ $hasExampleAudio ? $exampleAudioUrl : '' }}"
                        ></audio>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
