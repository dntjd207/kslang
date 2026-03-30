@php
    $isEdit = isset($slang);
    $levels = [
        1 => '1단계: 순한맛 (Mild)',
        2 => '2단계: 중간맛 (Moderate)',
        3 => '3단계: 매운맛 (Strong)',
        4 => '4단계: 극한맛 (Extreme)',
    ];
    $frequencies = ['Common', 'Occasional', 'Rare'];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- 좌측: 기본 정보 (2/3) --}}
    <div class="lg:col-span-2">
        <x-common.card title="기본 정보">
            <div class="space-y-4">
                <x-common.input
                    name="korean"
                    label="한국어 욕"
                    :value="$isEdit ? $slang->korean : ''"
                    placeholder="한국어 욕을 입력하세요"
                    :required="true"
                />

                @if ($isEdit)
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                            </svg>
                            <div class="flex-1">
                                <label for="ai_generation_hint" class="block text-sm font-medium text-indigo-900 mb-1">
                                    AI 참고 설명 (선택)
                                </label>
                                <p class="text-xs leading-5 text-indigo-800/80 mb-3">
                                    최신 유행어나 신조어처럼 AI가 의미를 모를 수 있을 때 참고할 설명입니다.
                                    자동 생성과 AI 재생성 시 함께 사용됩니다.
                                </p>
                                <textarea name="ai_generation_hint" id="ai_generation_hint" rows="3"
                                          placeholder="예: 밈이나 농담을 너무 과하게 반복해서 분위기를 망칠 때 쓰는 유행어"
                                          class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('ai_generation_hint') ? 'border-red-500' : 'border-indigo-200 bg-white' }}">{{ old('ai_generation_hint', $slang->ai_generation_hint) }}</textarea>
                                @error('ai_generation_hint')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <x-common.input
                    name="pronunciation"
                    label="영어 발음"
                    :value="$isEdit ? $slang->pronunciation : ''"
                    placeholder="영어 발음을 입력하세요 (예: ssi-bal)"
                    :required="true"
                />

                <div class="rounded-xl border border-gray-200 p-4 bg-gray-50/50">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">설명</h3>
                            <p class="text-xs text-gray-500 mt-1">영어 설명과 한글 설명을 함께 재생성합니다.</p>
                        </div>
                        @if ($isEdit)
                            <x-common.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                data-regenerate-section="descriptions"
                                data-loading-text="설명 재생성 중..."
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5.64 18.36A9 9 0 1018.36 5.64"/>
                                </svg>
                                설명 재생성
                            </x-common.button>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="english_description" class="block text-sm font-medium text-gray-700 mb-1">
                                영어 설명 <span class="text-red-500">*</span>
                            </label>
                            <textarea name="english_description" id="english_description" rows="3"
                                      placeholder="영어 설명을 입력하세요"
                                      class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('english_description') ? 'border-red-500' : 'border-gray-300' }}">{{ old('english_description', $isEdit ? $slang->english_description : '') }}</textarea>
                            @error('english_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="korean_description" class="block text-sm font-medium text-gray-700 mb-1">
                                한글 설명 <span class="text-red-500">*</span>
                            </label>
                            <textarea name="korean_description" id="korean_description" rows="3"
                                      placeholder="한글 설명을 입력하세요"
                                      class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('korean_description') ? 'border-red-500' : 'border-gray-300' }}">{{ old('korean_description', $isEdit ? $slang->korean_description : '') }}</textarea>
                            @error('korean_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                            레벨 <span class="text-red-500">*</span>
                        </label>
                        <select name="level" id="level"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $errors->has('level') ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">레벨 선택</option>
                            @foreach ($levels as $value => $label)
                                <option value="{{ $value }}" {{ old('level', $isEdit ? $slang->level : '') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="usage_frequency" class="block text-sm font-medium text-gray-700 mb-1">
                            사용 빈도 <span class="text-red-500">*</span>
                        </label>
                        <select name="usage_frequency" id="usage_frequency"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $errors->has('usage_frequency') ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">사용 빈도 선택</option>
                            @foreach ($frequencies as $freq)
                                <option value="{{ $freq }}" {{ old('usage_frequency', $isEdit ? $slang->usage_frequency : '') == $freq ? 'selected' : '' }}>
                                    {{ $freq }}
                                </option>
                            @endforeach
                        </select>
                        @error('usage_frequency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 bg-gray-50/50">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">사용 상황</h3>
                            <p class="text-xs text-gray-500 mt-1">한글 설명과 영어 번역을 함께 재생성합니다.</p>
                        </div>
                        @if ($isEdit)
                            <x-common.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                data-regenerate-section="usage_context"
                                data-loading-text="사용 상황 재생성 중..."
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5.64 18.36A9 9 0 1018.36 5.64"/>
                                </svg>
                                사용 상황 재생성
                            </x-common.button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div>
                            <label for="usage_context" class="block text-sm font-medium text-gray-700 mb-1">
                                사용 상황 (한글) <span class="text-red-500">*</span>
                            </label>
                            <textarea name="usage_context" id="usage_context" rows="3"
                                      placeholder="사용 상황을 입력하세요"
                                      class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('usage_context') ? 'border-red-500' : 'border-gray-300' }}">{{ old('usage_context', $isEdit ? $slang->usage_context : '') }}</textarea>
                            @error('usage_context')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="english_usage_context" class="block text-sm font-medium text-gray-700 mb-1">
                                사용 상황 영어 번역 <span class="text-red-500">*</span>
                            </label>
                            <textarea name="english_usage_context" id="english_usage_context" rows="3"
                                      placeholder="사용 상황 영어 번역을 입력하세요"
                                      class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('english_usage_context') ? 'border-red-500' : 'border-gray-300' }}">{{ old('english_usage_context', $isEdit ? $slang->english_usage_context : '') }}</textarea>
                            @error('english_usage_context')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </x-common.card>
    </div>

    {{-- 우측: 카테고리 + 음성 (1/3) --}}
    <div class="space-y-6">
        {{-- 카테고리 선택 --}}
        <x-common.card title="카테고리 선택">
            @if ($categories->isEmpty())
                <p class="text-sm text-gray-400">카테고리를 먼저 등록해주세요.</p>
            @else
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @php
                        $selectedCategoryIds = old('category_ids', $isEdit ? $slang->categories->pluck('id')->toArray() : []);
                    @endphp
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 px-2 py-1.5 rounded-md transition">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                   {{ in_array($category->id, (array) $selectedCategoryIds) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </x-common.card>

        {{-- 음성 파일 --}}
        @include('admin.slangs._audio-upload', ['slang' => $isEdit ? $slang : null])
    </div>
</div>

@include('admin.slangs._examples')

{{-- 하단 버튼 --}}
<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.slangs.index') }}">
        <x-common.button type="button" variant="secondary">취소</x-common.button>
    </a>
    <x-common.button type="submit">저장</x-common.button>
</div>
