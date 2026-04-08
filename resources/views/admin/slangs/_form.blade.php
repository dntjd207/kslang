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

{{-- 공개 SEO + 구글 검색 프리뷰 --}}
<div class="mt-6 grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-6">
    <x-common.card title="공개 SEO">
        <div class="space-y-4">
            <div class="flex flex-col gap-3 rounded-xl border border-blue-200 bg-blue-50/60 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">공개 슬랭 상세 페이지</h3>
                    <p class="mt-1 text-xs leading-5 text-blue-800/80">
                        `/korean-slang/{slug}` 형태의 공개 URL로 사용됩니다. 비워두면 저장 시 발음 기준으로 자동 생성됩니다.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($isEdit)
                        <x-common.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            data-regenerate-section="seo_fields"
                            data-loading-text="SEO 필드 생성 중..."
                        >
                            SEO 필드 AI 생성
                        </x-common.button>
                    @endif

                    @if ($isEdit && $slang->public_slug)
                        <a href="{{ route('slangs.public.show', ['slang' => $slang->public_slug]) }}"
                           target="_blank"
                           class="inline-flex items-center text-sm font-medium text-blue-700 transition hover:text-blue-900">
                            공개 페이지 보기
                        </a>
                    @endif
                </div>
            </div>

            <div>
                <label for="public_slug" class="block text-sm font-medium text-gray-700 mb-1">
                    공개 슬러그
                </label>
                <input
                    type="text"
                    name="public_slug"
                    id="public_slug"
                    value="{{ old('public_slug', $isEdit ? $slang->public_slug : '') }}"
                    placeholder="예: eok-kka"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('public_slug') ? 'border-red-500' : 'border-gray-300' }}"
                >
                @error('public_slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="public_title_en" class="block text-sm font-medium text-gray-700 mb-1">
                    공개 영어 제목
                </label>
                <input
                    type="text"
                    name="public_title_en"
                    id="public_title_en"
                    value="{{ old('public_title_en', $isEdit ? $slang->public_title_en : '') }}"
                    placeholder="예: What does 억까 mean in Korean?"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('public_title_en') ? 'border-red-500' : 'border-gray-300' }}"
                >
                @error('public_title_en')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="public_summary_en" class="block text-sm font-medium text-gray-700 mb-1">
                    공개 영어 요약
                </label>
                <textarea
                    name="public_summary_en"
                    id="public_summary_en"
                    rows="3"
                    placeholder="공개 상세 페이지 상단에 노출할 영어 요약"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('public_summary_en') ? 'border-red-500' : 'border-gray-300' }}"
                >{{ old('public_summary_en', $isEdit ? $slang->public_summary_en : '') }}</textarea>
                @error('public_summary_en')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div>
                    <label for="seo_title_en" class="block text-sm font-medium text-gray-700 mb-1">
                        SEO 제목
                    </label>
                    <input
                        type="text"
                        name="seo_title_en"
                        id="seo_title_en"
                        value="{{ old('seo_title_en', $isEdit ? $slang->seo_title_en : '') }}"
                        placeholder="검색 결과에 표시할 제목 (50~60자)"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('seo_title_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >
                    <p class="mt-1 text-xs text-gray-400" id="seo-title-counter"></p>
                    @error('seo_title_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seo_description_en" class="block text-sm font-medium text-gray-700 mb-1">
                        SEO 설명
                    </label>
                    <textarea
                        name="seo_description_en"
                        id="seo_description_en"
                        rows="3"
                        placeholder="검색 결과에 표시할 설명 (140~160자)"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('seo_description_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >{{ old('seo_description_en', $isEdit ? $slang->seo_description_en : '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-400" id="seo-desc-counter"></p>
                    @error('seo_description_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="seo_keywords_en" class="block text-sm font-medium text-gray-700 mb-1">
                    SEO 키워드
                </label>
                <input
                    type="text"
                    name="seo_keywords_en"
                    id="seo_keywords_en"
                    value="{{ old('seo_keywords_en', $isEdit ? $slang->seo_keywords_en : '') }}"
                    placeholder="쉼표로 구분된 영어 키워드 (예: Korean slang, meaning, pronunciation)"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('seo_keywords_en') ? 'border-red-500' : 'border-gray-300' }}"
                >
                @error('seo_keywords_en')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-common.card>

    {{-- 구글 검색 결과 시뮬레이션 --}}
    <div class="space-y-4 xl:sticky xl:top-24 xl:self-start">
        <x-common.card title="Google 검색 미리보기">
            <div id="serp-preview" class="space-y-5">
                {{-- Desktop SERP --}}
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Desktop</p>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-cyan-50">
                                <span class="text-[10px] font-bold text-cyan-700">K</span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-xs text-gray-800">kslang.com</p>
                                <p id="serp-url-desktop" class="truncate text-xs text-gray-500">https://kslang.com/korean-slang/...</p>
                            </div>
                        </div>
                        <h3 id="serp-title-desktop" class="text-lg leading-snug font-medium text-[#1a0dab] line-clamp-1">
                            SEO 제목을 입력하세요 | kslang
                        </h3>
                        <p id="serp-desc-desktop" class="mt-1 text-sm leading-relaxed text-[#4d5156] line-clamp-2">
                            SEO 설명을 입력하세요. 검색 결과에 이 텍스트가 표시됩니다.
                        </p>
                    </div>
                </div>

                {{-- Mobile SERP --}}
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Mobile</p>
                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan-50">
                                <span class="text-[9px] font-bold text-cyan-700">K</span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[11px] text-gray-800">kslang.com</p>
                                <p id="serp-url-mobile" class="truncate text-[11px] text-gray-500">https://kslang.com/korean-slang/...</p>
                            </div>
                        </div>
                        <h3 id="serp-title-mobile" class="text-base leading-snug font-medium text-[#1a0dab] line-clamp-2">
                            SEO 제목을 입력하세요 | kslang
                        </h3>
                        <p id="serp-desc-mobile" class="mt-1 text-[13px] leading-relaxed text-[#4d5156] line-clamp-3">
                            SEO 설명을 입력하세요. 검색 결과에 이 텍스트가 표시됩니다.
                        </p>
                    </div>
                </div>

                {{-- 길이 평가 --}}
                <div id="serp-analysis" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="mb-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">SEO 진단</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-start gap-2" id="serp-check-title">
                            <span class="mt-0.5 shrink-0">⚪</span>
                            <span class="text-gray-500">SEO 제목을 입력하면 진단 결과가 표시됩니다.</span>
                        </div>
                        <div class="flex items-start gap-2" id="serp-check-desc">
                            <span class="mt-0.5 shrink-0">⚪</span>
                            <span class="text-gray-500">SEO 설명을 입력하면 진단 결과가 표시됩니다.</span>
                        </div>
                        <div class="flex items-start gap-2" id="serp-check-slug">
                            <span class="mt-0.5 shrink-0">⚪</span>
                            <span class="text-gray-500">공개 슬러그를 입력하면 진단 결과가 표시됩니다.</span>
                        </div>
                        <div class="flex items-start gap-2" id="serp-check-keywords">
                            <span class="mt-0.5 shrink-0">⚪</span>
                            <span class="text-gray-500">SEO 키워드를 입력하면 진단 결과가 표시됩니다.</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-common.card>
    </div>
</div>

{{-- 하단 버튼 --}}
<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.slangs.index') }}">
        <x-common.button type="button" variant="secondary">취소</x-common.button>
    </a>
    <x-common.button type="submit">저장</x-common.button>
</div>
