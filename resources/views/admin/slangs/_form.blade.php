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

                <x-common.input
                    name="pronunciation"
                    label="영어 발음"
                    :value="$isEdit ? $slang->pronunciation : ''"
                    placeholder="영어 발음을 입력하세요 (예: ssi-bal)"
                    :required="true"
                />

                <div class="mb-4">
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

                <div class="mb-4">
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

                <div class="mb-4">
                    <label for="usage_context" class="block text-sm font-medium text-gray-700 mb-1">
                        사용 상황 <span class="text-red-500">*</span>
                    </label>
                    <textarea name="usage_context" id="usage_context" rows="2"
                              placeholder="사용 상황을 입력하세요"
                              class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y {{ $errors->has('usage_context') ? 'border-red-500' : 'border-gray-300' }}">{{ old('usage_context', $isEdit ? $slang->usage_context : '') }}</textarea>
                    @error('usage_context')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
