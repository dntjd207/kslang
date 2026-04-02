@php
    $isEdit = isset($blogPost) && $blogPost->exists;
    $selectedSlangIds = old('related_slang_ids', $isEdit ? $blogPost->slangs->pluck('id')->all() : []);
    $categoryName = old('category_name', $blogPost->category_name);
    $tagNames = old('tag_names', $blogPost->tag_names);
    $searchIntentLabels = [
        'meaning' => '뜻 설명형',
        'usage' => '사용법형',
        'comparison' => '비교형',
        'warning' => '주의형',
        'listicle' => '리스트형',
        'culture-context' => '문화/맥락형',
    ];
    $statusBadgeClasses = [
        \App\Models\BlogPost::STATUS_DRAFT => 'bg-amber-100 text-amber-800',
        \App\Models\BlogPost::STATUS_PUBLISHED => 'bg-emerald-100 text-emerald-800',
        \App\Models\BlogPost::STATUS_ARCHIVED => 'bg-slate-200 text-slate-700',
    ];
    $translationBadgeClasses = [
        \App\Models\BlogPost::TRANSLATION_NONE => 'bg-slate-100 text-slate-700',
        \App\Models\BlogPost::TRANSLATION_SYNCED => 'bg-blue-100 text-blue-800',
        \App\Models\BlogPost::TRANSLATION_OUTDATED => 'bg-rose-100 text-rose-800',
    ];
@endphp

<input
    type="hidden"
    name="blog_post_id"
    id="blog_post_id"
    value="{{ $isEdit ? $blogPost->id : '' }}"
>

<input
    type="hidden"
    name="translation_model"
    id="translation_model"
    value="{{ old('translation_model', $blogPost->translation_model) }}"
>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
    <div class="space-y-6">
        <x-common.card title="콘텐츠 전략">
            <div class="space-y-4">
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-indigo-900">AI 초안 생성 흐름</h3>
                            <p class="mt-1 text-xs leading-5 text-indigo-800/80">
                                핵심 키워드와 브리프를 입력한 뒤 AI 초안을 만들고, 한국어를 수정한 후 영어 공개본을 다시 생성하세요.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-common.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                id="generate-blog-draft-button"
                                data-loading-text="AI 초안 생성 중..."
                            >
                                AI 한국어+영어 초안 생성
                            </x-common.button>

                            <x-common.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                id="translate-blog-button"
                                data-loading-text="영어 번역 생성 중..."
                            >
                                영어 공개본 재생성
                            </x-common.button>
                        </div>
                    </div>

                    <div id="blog-ai-feedback" class="hidden rounded-lg border px-3 py-2 text-sm"></div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label for="primary_keyword" class="block text-sm font-medium text-gray-700 mb-1">
                            핵심 키워드
                        </label>
                        <input
                            type="text"
                            name="primary_keyword"
                            id="primary_keyword"
                            value="{{ old('primary_keyword', $blogPost->primary_keyword) }}"
                            placeholder="예: what does 억까 mean in Korean"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('primary_keyword') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                        @error('primary_keyword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="search_intent" class="block text-sm font-medium text-gray-700 mb-1">
                            검색 의도
                        </label>
                        <select
                            name="search_intent"
                            id="search_intent"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('search_intent') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                            <option value="">선택 안 함</option>
                            @foreach ($searchIntentOptions as $option)
                                <option value="{{ $option }}" @selected(old('search_intent', $blogPost->search_intent) === $option)>
                                    {{ $searchIntentLabels[$option] ?? $option }}
                                </option>
                            @endforeach
                        </select>
                        @error('search_intent')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <div>
                        <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">
                            블로그 카테고리
                        </label>
                        <input
                            type="text"
                            name="category_name"
                            id="category_name"
                            list="blog-category-options"
                            value="{{ $categoryName }}"
                            placeholder="예: Meaning"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('category_name') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                        @error('category_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tag_names" class="block text-sm font-medium text-gray-700 mb-1">
                            태그
                        </label>
                        <input
                            type="text"
                            name="tag_names"
                            id="tag_names"
                            list="blog-tag-options"
                            value="{{ $tagNames }}"
                            placeholder="쉼표로 구분해서 입력하세요. 예: korean slang, k-drama, internet slang"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('tag_names') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                        <p class="mt-1 text-xs text-gray-500">자동 임시저장과 수동 저장 모두 쉼표 기준으로 정리됩니다.</p>
                        @error('tag_names')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div id="tag-chip-preview" class="mt-2 flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <div>
                    <label for="content_brief_ko" class="block text-sm font-medium text-gray-700 mb-1">
                        콘텐츠 브리프 (한국어)
                    </label>
                    <textarea
                        name="content_brief_ko"
                        id="content_brief_ko"
                        rows="4"
                        placeholder="운영자 메모, 글에서 꼭 다룰 포인트, 금지 표현, CTA 방향 등을 한국어로 적어주세요."
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('content_brief_ko') ? 'border-red-500' : 'border-gray-300' }}"
                    >{{ old('content_brief_ko', $blogPost->content_brief_ko) }}</textarea>
                    @error('content_brief_ko')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.card>

        <x-common.card title="한국어 원본">
            <div class="space-y-4">
                <div>
                    <label for="title_ko" class="block text-sm font-medium text-gray-700 mb-1">
                        한국어 제목
                    </label>
                    <input
                        type="text"
                        name="title_ko"
                        id="title_ko"
                        value="{{ old('title_ko', $blogPost->title_ko) }}"
                        placeholder="운영자가 검수할 기준 제목"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title_ko') ? 'border-red-500' : 'border-gray-300' }}"
                    >
                    @error('title_ko')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt_ko" class="block text-sm font-medium text-gray-700 mb-1">
                        한국어 요약
                    </label>
                    <textarea
                        name="excerpt_ko"
                        id="excerpt_ko"
                        rows="3"
                        placeholder="글의 핵심을 2~3문장으로 요약"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('excerpt_ko') ? 'border-red-500' : 'border-gray-300' }}"
                    >{{ old('excerpt_ko', $blogPost->excerpt_ko) }}</textarea>
                    @error('excerpt_ko')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body_ko_editor" class="block text-sm font-medium text-gray-700 mb-1">
                        한국어 본문
                    </label>
                    <textarea
                        name="body_ko"
                        id="body_ko_editor"
                        class="blog-rich-editor w-full"
                    >{!! old('body_ko', $blogPost->body_ko) !!}</textarea>
                    @error('body_ko')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.card>

        <x-common.card title="영어 공개본">
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4">
                    <p class="text-sm font-semibold text-gray-800">운영 규칙</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        한국어 기준으로 검수한 뒤 영어를 다시 생성하세요. 한국어가 바뀌면 발행 전 재번역이 필요합니다.
                    </p>
                </div>

                <div>
                    <label for="title_en" class="block text-sm font-medium text-gray-700 mb-1">
                        영어 제목
                    </label>
                    <input
                        type="text"
                        name="title_en"
                        id="title_en"
                        value="{{ old('title_en', $blogPost->title_en) }}"
                        placeholder="예: What Does 억까 Mean in Korean?"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >
                    @error('title_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt_en" class="block text-sm font-medium text-gray-700 mb-1">
                        영어 요약
                    </label>
                    <textarea
                        name="excerpt_en"
                        id="excerpt_en"
                        rows="3"
                        placeholder="Search snippet and article intro"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('excerpt_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >{{ old('excerpt_en', $blogPost->excerpt_en) }}</textarea>
                    @error('excerpt_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body_en_editor" class="block text-sm font-medium text-gray-700 mb-1">
                        영어 본문
                    </label>
                    <textarea
                        name="body_en"
                        id="body_en_editor"
                        class="blog-rich-editor w-full"
                    >{!! old('body_en', $blogPost->body_en) !!}</textarea>
                    @error('body_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.card>

        <x-common.card title="SEO 설정">
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                            공개 슬러그
                        </label>
                        <input
                            type="text"
                            name="slug"
                            id="slug"
                            value="{{ old('slug', $blogPost->slug) }}"
                            placeholder="예: what-does-eok-kka-mean-in-korean"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('slug') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="canonical_url" class="block text-sm font-medium text-gray-700 mb-1">
                            Canonical URL
                        </label>
                        <input
                            type="url"
                            name="canonical_url"
                            id="canonical_url"
                            value="{{ old('canonical_url', $blogPost->canonical_url) }}"
                            placeholder="비워두면 현재 URL 기준"
                            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('canonical_url') ? 'border-red-500' : 'border-gray-300' }}"
                        >
                        @error('canonical_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="seo_title_en" class="block text-sm font-medium text-gray-700 mb-1">
                        SEO 제목
                    </label>
                    <input
                        type="text"
                        name="seo_title_en"
                        id="seo_title_en"
                        value="{{ old('seo_title_en', $blogPost->seo_title_en) }}"
                        placeholder="Google 검색 결과용 제목"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('seo_title_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >
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
                        placeholder="Google 검색 결과용 설명"
                        class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y {{ $errors->has('seo_description_en') ? 'border-red-500' : 'border-gray-300' }}"
                    >{{ old('seo_description_en', $blogPost->seo_description_en) }}</textarea>
                    @error('seo_description_en')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.card>

        <x-common.card title="관련 공개 슬랭">
            @if ($relatedSlangs->isEmpty())
                <p class="text-sm text-gray-500">연결할 공개 슬랭이 아직 없습니다.</p>
            @else
                <div class="grid max-h-72 grid-cols-1 gap-2 overflow-y-auto rounded-lg border border-gray-200 p-3 sm:grid-cols-2">
                    @foreach ($relatedSlangs as $slang)
                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 px-3 py-2 transition hover:border-indigo-300 hover:bg-indigo-50/40">
                            <input
                                type="checkbox"
                                name="related_slang_ids[]"
                                value="{{ $slang->id }}"
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @checked(in_array($slang->id, (array) $selectedSlangIds, true))
                            >

                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-gray-800">{{ $slang->korean }}</span>
                                <span class="block text-xs text-gray-500">{{ $slang->pronunciation }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </x-common.card>
    </div>

    <div class="space-y-6">
        <x-common.card title="자동 임시저장">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span id="autosave-dot" class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    <p id="autosave-status-text" class="text-sm font-medium text-gray-700">
                        @if (! $isEdit || $blogPost->status !== \App\Models\BlogPost::STATUS_PUBLISHED)
                            자동 임시저장 대기 중
                        @else
                            발행된 글은 자동 서버 저장이 비활성화됩니다.
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 text-sm text-gray-600">
                    <p id="autosave-last-saved-label">
                        마지막 자동 저장:
                        <span class="font-medium text-gray-800">
                            {{ $blogPost->last_auto_saved_at?->format('Y-m-d H:i:s') ?? '-' }}
                        </span>
                    </p>
                </div>

                <p class="text-xs leading-5 text-gray-500">
                    새 글과 초안/보관 글은 입력 후 잠시 멈추면 자동으로 draft 저장됩니다. 발행된 글은 공개 중인 콘텐츠 보호를 위해 자동 서버 저장을 하지 않습니다.
                </p>
            </div>
        </x-common.card>

        <x-common.card title="발행 상태">
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    <span id="status-badge" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$blogPost->status ?: \App\Models\BlogPost::STATUS_DRAFT] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $blogPost->status_label ?: '임시 저장' }}
                    </span>

                    <span id="translation-status-badge" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $translationBadgeClasses[$blogPost->translation_status ?: \App\Models\BlogPost::TRANSLATION_NONE] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $blogPost->translation_status_label ?: '영문 없음' }}
                    </span>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">카테고리</dt>
                        <dd class="text-right font-medium text-gray-800">{{ $blogPost->category_name ?: '-' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">마지막 한국어 수정</dt>
                        <dd class="text-right font-medium text-gray-800">{{ $blogPost->last_ko_updated_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">영문 동기화 시각</dt>
                        <dd class="text-right font-medium text-gray-800">{{ $blogPost->en_synced_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">발행 시각</dt>
                        <dd class="text-right font-medium text-gray-800">{{ $blogPost->published_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">번역 모델</dt>
                        <dd id="translation-model-label" class="text-right font-medium text-gray-800">{{ $blogPost->translation_model ?: '-' }}</dd>
                    </div>
                </dl>

                @if ($isEdit && $blogPost->isPublished())
                    <a href="{{ route('blog.show', ['blogPost' => $blogPost->slug]) }}" target="_blank" class="inline-flex">
                        <x-common.button type="button" variant="secondary" size="sm">
                            공개 페이지 열기
                        </x-common.button>
                    </a>
                @endif
            </div>
        </x-common.card>

        <x-common.card title="검색 결과 미리보기">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p id="seo-preview-title" class="text-lg leading-snug text-blue-700">
                    {{ old('seo_title_en', $blogPost->resolved_seo_title ?? 'SEO title preview') }}
                </p>
                <p id="seo-preview-url" class="mt-1 text-sm text-emerald-700">
                    {{ url('/blog/' . ($blogPost->slug ?: 'your-slug')) }}
                </p>
                <p id="seo-preview-description" class="mt-2 text-sm leading-6 text-gray-600">
                    {{ old('seo_description_en', $blogPost->resolved_seo_description ?? 'SEO description preview') }}
                </p>
            </div>
        </x-common.card>

        <x-common.card title="SEO 작성 체크리스트">
            <div class="space-y-3 text-sm text-gray-600">
                <p>1. 제목은 한 검색 의도에 집중하고 60자 안팎으로 유지합니다.</p>
                <p>2. 본문은 H2 중심 구조로 나누고, H1은 페이지 제목 하나만 사용합니다.</p>
                <p>3. 요약 문단에서 핵심 키워드를 자연스럽게 포함합니다.</p>
                <p>4. 관련 슬랭 상세 페이지를 최소 1개 이상 연결합니다.</p>
                <p>5. 한국어를 수정한 뒤에는 영어를 다시 생성하거나 직접 갱신합니다.</p>
                <p>6. 카테고리와 태그는 검색 주제 묶음을 명확히 보여주는 방향으로 간단하게 유지합니다.</p>
            </div>
        </x-common.card>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-end gap-3">
    @if ($isEdit)
        <button type="submit" name="save_action" value="archive" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            보관
        </button>
    @endif

    <x-common.button type="submit" variant="secondary" name="save_action" value="draft">
        임시 저장
    </x-common.button>

    <x-common.button type="submit" name="save_action" value="publish">
        발행
    </x-common.button>
</div>

<datalist id="blog-category-options">
    @foreach ($categoryOptions as $categoryOption)
        <option value="{{ $categoryOption }}"></option>
    @endforeach
</datalist>

<datalist id="blog-tag-options">
    @foreach ($tagOptions as $tagOption)
        <option value="{{ $tagOption }}"></option>
    @endforeach
</datalist>
