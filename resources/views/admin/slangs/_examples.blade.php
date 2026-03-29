@php
    $existingExamples = old('examples', isset($slang) ? $slang->examples->map(fn($e) => [
        'id' => $e->id,
        'korean_example' => $e->korean_example,
        'english_example' => $e->english_example,
    ])->toArray() : []);
@endphp

<div class="mt-6">
    <x-common.card>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">사용 예문</h2>
                @if (isset($slang))
                    <p class="text-xs text-gray-500 mt-1">AI 버튼을 누르면 예문 3개가 추가 생성됩니다.</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if (isset($slang))
                    <x-common.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        data-regenerate-section="examples"
                        data-loading-text="예문 생성 중..."
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5.64 18.36A9 9 0 1018.36 5.64"/>
                        </svg>
                        AI 예문 3개 추가
                    </x-common.button>
                @endif

                <x-common.button type="button" variant="secondary" size="sm" id="add-example">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    예문 추가
                </x-common.button>
            </div>
        </div>

        <div id="examples-container" class="space-y-3">
            @foreach ($existingExamples as $idx => $example)
                @include('admin.slangs._example-row', ['index' => $idx, 'example' => $example])
            @endforeach
        </div>

        <div id="no-examples-msg" class="{{ count($existingExamples) > 0 ? 'hidden' : '' }} text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg mt-3">
            <p>등록된 예문이 없습니다.</p>
            <p class="text-sm mt-1">"예문 추가" 버튼을 눌러 예문을 추가하세요.</p>
        </div>
    </x-common.card>
</div>
