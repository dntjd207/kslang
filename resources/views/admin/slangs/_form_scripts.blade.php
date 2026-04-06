const MAX_EXAMPLES = 50;
const AI_EXAMPLES_BATCH_SIZE = 3;
let exampleIndex = document.querySelectorAll('.example-row').length;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function getSlangId() {
    return document.getElementById('slang-form')?.dataset.slangId ?? '';
}

function showFormToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-[60] transition-opacity duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function getInputValue(id) {
    return document.getElementById(id)?.value.trim() ?? '';
}

function getSelectedOptionText(id) {
    const select = document.getElementById(id);
    const selectedOption = select?.selectedOptions?.[0];

    if (!selectedOption) {
        return '';
    }

    return selectedOption.textContent.trim();
}

async function copyTextToClipboard(text) {
    if (navigator.clipboard?.writeText && window.isSecureContext) {
        await navigator.clipboard.writeText(text);

        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) {
        throw new Error('클립보드 복사에 실패했습니다.');
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function updateNoExamplesState() {
    const container = document.getElementById('examples-container');
    const noMessage = document.getElementById('no-examples-msg');

    if (!container || !noMessage) {
        return;
    }

    noMessage.classList.toggle('hidden', container.querySelectorAll('.example-row').length > 0);
}

function buildExampleAudioSectionMarkup(index, example = {}) {
    if (!getSlangId()) {
        return '';
    }

    const hasAudio = Boolean(example.audio_file && example.audio_url);
    const helperText = example.id
        ? '생성하면 즉시 저장됩니다.'
        : '새 예문은 생성 후 전체 저장 시 DB에 반영됩니다.';

    return `
        <div class="rounded-lg border border-gray-200 bg-white p-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">예문 mp3</p>
                    <p class="mt-1 text-xs text-gray-500">${escapeHtml(helperText)}</p>
                </div>
                <button
                    type="button"
                    class="generate-example-audio inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    data-loading-text="예문 mp3 생성 중..."
                >
                    예문 mp3 생성
                </button>
            </div>

            <p class="example-audio-empty mt-3 text-xs text-gray-400 ${hasAudio ? 'hidden' : ''}">
                아직 생성된 예문 mp3가 없습니다.
            </p>

            <div class="example-audio-player-wrapper mt-3 ${hasAudio ? '' : 'hidden'}">
                <audio class="example-audio-player w-full" controls preload="metadata" src="${escapeHtml(example.audio_url ?? '')}"></audio>
            </div>
        </div>
    `;
}

function buildExampleRowMarkup(index, example = {}) {
    const hiddenId = example.id
        ? `<input type="hidden" name="examples[${index}][id]" value="${escapeHtml(example.id)}">`
        : '';
    const audioFile = escapeHtml(example.audio_file ?? '');
    const audioDisk = escapeHtml(example.audio_disk ?? '');

    return `
        ${hiddenId}
        <input type="hidden" name="examples[${index}][audio_file]" value="${audioFile}">
        <input type="hidden" name="examples[${index}][audio_disk]" value="${audioDisk}">
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
                               name="examples[${index}][korean_example]"
                               value="${escapeHtml(example.korean_example ?? '')}"
                               placeholder="예: 씨발, 또 늦었어!"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">영어 번역</label>
                        <input type="text"
                               name="examples[${index}][english_example]"
                               value="${escapeHtml(example.english_example ?? '')}"
                               placeholder="예: F**k, I'm late again!"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                ${buildExampleAudioSectionMarkup(index, example)}
            </div>
        </div>
    `;
}

function appendExampleRow(example = {}, shouldFocus = false) {
    const container = document.getElementById('examples-container');

    if (!container) {
        return false;
    }

    if (container.querySelectorAll('.example-row').length >= MAX_EXAMPLES) {
        return false;
    }

    const row = document.createElement('div');
    row.className = 'example-row rounded-lg border border-gray-200 bg-gray-50 p-4';
    row.dataset.index = exampleIndex;
    row.innerHTML = buildExampleRowMarkup(exampleIndex, example);
    container.appendChild(row);

    exampleIndex++;
    updateNoExamplesState();
    initExampleSortable();

    if (shouldFocus) {
        row.querySelector('input[type="text"]')?.focus();
    }

    return true;
}

document.getElementById('add-example')?.addEventListener('click', function () {
    if (!appendExampleRow({}, true)) {
        showFormToast(`예문은 최대 ${MAX_EXAMPLES}개까지 추가할 수 있습니다.`, 'error');
    }
});

document.addEventListener('click', function (event) {
    const removeButton = event.target.closest('.remove-example');

    if (!removeButton) {
        return;
    }

    removeButton.closest('.example-row')?.remove();
    reindexExamples();
    updateNoExamplesState();
});

function reindexExamples() {
    document.querySelectorAll('.example-row').forEach((row, index) => {
        row.dataset.index = index;

        row.querySelectorAll('input[name*="korean_example"]').forEach((input) => {
            input.name = `examples[${index}][korean_example]`;
        });

        row.querySelectorAll('input[name*="english_example"]').forEach((input) => {
            input.name = `examples[${index}][english_example]`;
        });

        const hiddenId = row.querySelector('input[name*="[id]"]');
        if (hiddenId) {
            hiddenId.name = `examples[${index}][id]`;
        }

        row.querySelectorAll('input[name*="[audio_file]"]').forEach((input) => {
            input.name = `examples[${index}][audio_file]`;
        });

        row.querySelectorAll('input[name*="[audio_disk]"]').forEach((input) => {
            input.name = `examples[${index}][audio_disk]`;
        });
    });

    exampleIndex = document.querySelectorAll('.example-row').length;
}

function initExampleSortable() {
    const container = document.getElementById('examples-container');
    if (!container) {
        return;
    }

    if (container._sortable) {
        container._sortable.destroy();
    }

    container._sortable = new Sortable(container, {
        handle: '.drag-handle-example',
        animation: 150,
        ghostClass: 'opacity-50',
        chosenClass: 'shadow-lg',
        onEnd() {
            reindexExamples();
        },
    });
}

function collectExamplesFromForm() {
    return Array.from(document.querySelectorAll('.example-row'))
        .map((row) => {
            return {
                id: row.querySelector('input[name*="[id]"]')?.value ?? '',
                korean_example: row.querySelector('input[name*="[korean_example]"]')?.value ?? '',
                english_example: row.querySelector('input[name*="[english_example]"]')?.value ?? '',
            };
        })
        .filter((example) => example.korean_example.trim() !== '' || example.english_example.trim() !== '');
}

function collectSelectedCategoryNames() {
    return Array.from(document.querySelectorAll('input[name="category_ids[]"]:checked'))
        .map((checkbox) => checkbox.closest('label')?.querySelector('span')?.textContent?.trim() ?? '')
        .filter((name) => name !== '');
}

function buildCardNewsCopyText() {
    const korean = getInputValue('korean');
    const pronunciation = getInputValue('pronunciation');
    const level = getSelectedOptionText('level');
    const usageFrequency = getSelectedOptionText('usage_frequency');
    const koreanDescription = getInputValue('korean_description');
    const englishDescription = getInputValue('english_description');
    const usageContext = getInputValue('usage_context');
    const englishUsageContext = getInputValue('english_usage_context');
    const examples = collectExamplesFromForm().map((example) => ({
        korean_example: example.korean_example.trim(),
        english_example: example.english_example.trim(),
    }));

    if (korean === '') {
        throw new Error('한국어 욕을 입력한 뒤 다시 시도해주세요.');
    }

    if (koreanDescription === '' && englishDescription === '' && examples.length === 0) {
        throw new Error('복사할 설명이나 예문이 없습니다.');
    }

    const lines = [
        '카드뉴스 제작용 단어 정리',
        '',
        '[기본 정보]',
        `단어: ${korean}`,
    ];

    if (pronunciation !== '') {
        lines.push(`발음: ${pronunciation}`);
    }

    if (level !== '' && level !== '레벨 선택') {
        lines.push(`레벨: ${level}`);
    }

    if (usageFrequency !== '' && usageFrequency !== '사용 빈도 선택') {
        lines.push(`사용 빈도: ${usageFrequency}`);
    }

    if (koreanDescription !== '' || englishDescription !== '') {
        lines.push('', '[설명]');

        if (koreanDescription !== '') {
            lines.push('한글 설명:', koreanDescription);
        }

        if (englishDescription !== '') {
            if (koreanDescription !== '') {
                lines.push('');
            }

            lines.push('영어 설명:', englishDescription);
        }
    }

    if (usageContext !== '' || englishUsageContext !== '') {
        lines.push('', '[사용 상황]');

        if (usageContext !== '') {
            lines.push('한글:', usageContext);
        }

        if (englishUsageContext !== '') {
            if (usageContext !== '') {
                lines.push('');
            }

            lines.push('영어:', englishUsageContext);
        }
    }

    if (examples.length > 0) {
        lines.push('', '[예문]');

        examples.forEach((example, index) => {
            if (index > 0) {
                lines.push('');
            }

            lines.push(`${index + 1}.`);

            if (example.korean_example !== '') {
                lines.push(`- 한국어: ${example.korean_example}`);
            }

            if (example.english_example !== '') {
                lines.push(`- 영어 번역: ${example.english_example}`);
            }
        });
    }

    return lines.join('\n').trim();
}

function getRegenerationPayload(section) {
    return {
        section,
        korean: document.getElementById('korean')?.value ?? '',
        ai_generation_hint: document.getElementById('ai_generation_hint')?.value ?? '',
        pronunciation: document.getElementById('pronunciation')?.value ?? '',
        english_description: document.getElementById('english_description')?.value ?? '',
        korean_description: document.getElementById('korean_description')?.value ?? '',
        level: document.getElementById('level')?.value ?? '',
        usage_frequency: document.getElementById('usage_frequency')?.value ?? '',
        usage_context: document.getElementById('usage_context')?.value ?? '',
        english_usage_context: document.getElementById('english_usage_context')?.value ?? '',
        public_slug: document.getElementById('public_slug')?.value ?? '',
        public_title_en: document.getElementById('public_title_en')?.value ?? '',
        public_summary_en: document.getElementById('public_summary_en')?.value ?? '',
        seo_title_en: document.getElementById('seo_title_en')?.value ?? '',
        seo_description_en: document.getElementById('seo_description_en')?.value ?? '',
        seo_keywords_en: document.getElementById('seo_keywords_en')?.value ?? '',
        category_names: collectSelectedCategoryNames(),
        examples: collectExamplesFromForm(),
    };
}

function setButtonLoading(button, isLoading) {
    if (!button.dataset.originalHtml) {
        button.dataset.originalHtml = button.innerHTML;
    }

    if (isLoading) {
        button.disabled = true;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-1 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            ${button.dataset.loadingText ?? '처리 중...'}
        `;

        return;
    }

    button.disabled = false;
    button.innerHTML = button.dataset.originalHtml ?? button.innerHTML;
}

function updateExampleAudioState(row, result) {
    const audioFileInput = row.querySelector('input[name*="[audio_file]"]');
    const audioDiskInput = row.querySelector('input[name*="[audio_disk]"]');
    const emptyState = row.querySelector('.example-audio-empty');
    const playerWrapper = row.querySelector('.example-audio-player-wrapper');
    const player = row.querySelector('.example-audio-player');

    if (!audioFileInput || !audioDiskInput || !playerWrapper || !player) {
        throw new Error('예문 오디오 영역을 찾지 못했습니다.');
    }

    audioFileInput.value = result.audio_file ?? '';
    audioDiskInput.value = result.audio_disk ?? '';
    player.src = result.audio_url ?? '';
    player.load();
    playerWrapper.classList.remove('hidden');
    emptyState?.classList.add('hidden');
}

async function handleGenerateExampleAudio(button, row) {
    const slangId = getSlangId();
    const koreanInput = row.querySelector('input[name*="[korean_example]"]');
    const exampleId = row.querySelector('input[name*="[id]"]')?.value ?? '';

    if (!slangId) {
        showFormToast('저장된 슬랭에서만 예문 mp3를 생성할 수 있습니다.', 'error');
        return;
    }

    if (!koreanInput?.value.trim()) {
        showFormToast('한국어 예문을 입력한 뒤 다시 시도해주세요.', 'error');
        koreanInput?.focus();
        return;
    }

    setButtonLoading(button, true);

    try {
        const response = await fetch(`/admin/slangs/${slangId}/generate-example-audio`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                example_id: exampleId || null,
                example_index: Number(row.dataset.index ?? 0),
                text: koreanInput.value.trim(),
            }),
        });

        const result = await response.json().catch(() => null);

        if (!response.ok || !result?.success) {
            throw new Error(result?.message ?? '예문 mp3 생성에 실패했습니다.');
        }

        updateExampleAudioState(row, result.result ?? {});
        showFormToast(result.message ?? '예문 mp3 생성이 완료되었습니다.');
    } catch (error) {
        showFormToast(error.message ?? '예문 mp3 생성에 실패했습니다.', 'error');
    } finally {
        setButtonLoading(button, false);
    }
}

function applyRegeneratedSection(section, data) {
    if (section === 'descriptions') {
        document.getElementById('english_description').value = data.english_description ?? '';
        document.getElementById('korean_description').value = data.korean_description ?? '';
        return;
    }

    if (section === 'usage_context') {
        document.getElementById('usage_context').value = data.usage_context ?? '';
        document.getElementById('english_usage_context').value = data.english_usage_context ?? '';
        return;
    }

    if (section === 'examples') {
        const examples = Array.isArray(data.examples) ? data.examples : [];

        if (examples.length === 0) {
            throw new Error('AI가 추가 예문을 생성하지 못했습니다.');
        }

        let appendedCount = 0;

        examples.forEach((example) => {
            if (appendExampleRow(example, appendedCount === 0)) {
                appendedCount++;
            }
        });

        if (appendedCount === 0) {
            throw new Error('예문을 더 추가할 수 없습니다.');
        }

        return;
    }

    if (section === 'seo_fields') {
        document.getElementById('public_slug').value = data.public_slug ?? '';
        document.getElementById('public_title_en').value = data.public_title_en ?? '';
        document.getElementById('public_summary_en').value = data.public_summary_en ?? '';
        document.getElementById('seo_title_en').value = data.seo_title_en ?? '';
        document.getElementById('seo_description_en').value = data.seo_description_en ?? '';
        document.getElementById('seo_keywords_en').value = data.seo_keywords_en ?? '';
        updateSeoCounters();
        return;
    }

    if (section === 'faq') {
        renderFaqItems(data.faq_items ?? []);
    }
}

function renderFaqItems(items) {
    const container = document.getElementById('faq-container');

    if (!container) {
        return;
    }

    if (!Array.isArray(items) || items.length === 0) {
        container.innerHTML = '<div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-400">AI가 FAQ를 생성하지 못했습니다.</div>';
        return;
    }

    container.innerHTML = items.map((item) => `
        <div class="rounded-xl border border-gray-200 p-4 mb-3">
            <p class="text-sm font-semibold text-gray-900">Q. ${item.question ?? ''}</p>
            <p class="mt-2 text-sm leading-6 text-gray-600">${item.answer ?? ''}</p>
        </div>
    `).join('');
}

document.getElementById('btn-generate-faq')?.addEventListener('click', async function () {
    const slangId = getSlangId();

    if (!slangId) {
        showFormToast('저장된 슬랭에서만 FAQ 생성을 사용할 수 있습니다.', 'error');
        return;
    }

    setButtonLoading(this, true);

    try {
        const response = await fetch(`/admin/slangs/${slangId}/regenerate-section`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(getRegenerationPayload('faq')),
        });

        const result = await response.json().catch(() => null);

        if (!response.ok || !result?.success) {
            throw new Error(result?.message ?? 'FAQ 생성에 실패했습니다.');
        }

        renderFaqItems(result.data?.faq_items ?? []);
        showFormToast(result.message ?? 'FAQ가 생성되었습니다.');
    } catch (error) {
        showFormToast(error.message ?? 'FAQ 생성에 실패했습니다.', 'error');
    } finally {
        setButtonLoading(this, false);
    }
});

document.querySelector('[data-copy-card-news]')?.addEventListener('click', async function () {
    try {
        const copyText = buildCardNewsCopyText();

        await copyTextToClipboard(copyText);
        showFormToast('카드뉴스용 텍스트를 클립보드에 복사했습니다.');
    } catch (error) {
        showFormToast(error.message ?? '클립보드 복사에 실패했습니다.', 'error');
    }
});

document.querySelectorAll('[data-regenerate-section]').forEach((button) => {
    button.addEventListener('click', async function () {
        const slangId = getSlangId();
        const section = this.dataset.regenerateSection;
        const koreanInput = document.getElementById('korean');

        if (!slangId) {
            showFormToast('저장된 슬랭에서만 AI 재생성을 사용할 수 있습니다.', 'error');
            return;
        }

        if (!koreanInput?.value.trim()) {
            showFormToast('한국어 욕을 입력한 뒤 다시 시도해주세요.', 'error');
            koreanInput?.focus();
            return;
        }

        if (section === 'examples') {
            const currentCount = document.querySelectorAll('.example-row').length;

            if (currentCount > MAX_EXAMPLES - AI_EXAMPLES_BATCH_SIZE) {
                showFormToast(`예문은 최대 ${MAX_EXAMPLES}개까지라 AI 예문 3개를 추가할 수 없습니다.`, 'error');
                return;
            }
        }

        setButtonLoading(this, true);

        try {
            const response = await fetch(`/admin/slangs/${slangId}/regenerate-section`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(getRegenerationPayload(section)),
            });

            const result = await response.json().catch(() => null);

            if (!response.ok || !result?.success) {
                throw new Error(result?.message ?? 'AI 재생성에 실패했습니다.');
            }

            applyRegeneratedSection(section, result.data ?? {});
            showFormToast(result.message ?? 'AI 재생성이 완료되었습니다.');
        } catch (error) {
            showFormToast(error.message ?? 'AI 재생성에 실패했습니다.', 'error');
        } finally {
            setButtonLoading(this, false);
        }
    });
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.generate-example-audio');

    if (!button) {
        return;
    }

    const row = button.closest('.example-row');

    if (!row) {
        return;
    }

    handleGenerateExampleAudio(button, row);
});

function updateSeoCounters() {
    const titleInput = document.getElementById('seo_title_en');
    const descInput = document.getElementById('seo_description_en');
    const titleCounter = document.getElementById('seo-title-counter');
    const descCounter = document.getElementById('seo-desc-counter');

    if (titleInput && titleCounter) {
        const len = titleInput.value.length;
        const color = len === 0 ? 'text-gray-400' : (len >= 50 && len <= 60 ? 'text-emerald-600' : (len > 60 ? 'text-red-500' : 'text-amber-500'));
        titleCounter.className = `mt-1 text-xs ${color}`;
        titleCounter.textContent = len > 0 ? `${len}/60자 (뒤에 " | kslang" 자동 추가)` : '';
    }

    if (descInput && descCounter) {
        const len = descInput.value.length;
        const color = len === 0 ? 'text-gray-400' : (len >= 140 && len <= 160 ? 'text-emerald-600' : (len > 160 ? 'text-red-500' : 'text-amber-500'));
        descCounter.className = `mt-1 text-xs ${color}`;
        descCounter.textContent = len > 0 ? `${len}/160자` : '';
    }
}

document.getElementById('seo_title_en')?.addEventListener('input', updateSeoCounters);
document.getElementById('seo_description_en')?.addEventListener('input', updateSeoCounters);
updateSeoCounters();

updateNoExamplesState();
initExampleSortable();

// === 음성 파일 업로드 관리 ===
(function () {
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    const ALLOWED_TYPES = ['audio/mpeg'];
    const ALLOWED_EXTENSIONS = ['.mp3'];

    const fileInput = document.getElementById('audio-file-input');
    const dropArea = document.getElementById('audio-drop-area');
    const selectBtn = document.getElementById('audio-select-btn');

    if (!fileInput || !dropArea) {
        return;
    }

    selectBtn?.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    dropArea.addEventListener('dragover', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.classList.add('border-indigo-400', 'bg-indigo-50/50');
    });

    dropArea.addEventListener('dragleave', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.classList.remove('border-indigo-400', 'bg-indigo-50/50');
    });

    dropArea.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.classList.remove('border-indigo-400', 'bg-indigo-50/50');

        if (event.dataTransfer.files.length > 0) {
            handleFileSelect(event.dataTransfer.files[0]);
        }
    });

    document.getElementById('audio-change-btn')?.addEventListener('click', function () {
        fileInput.click();
    });

    document.getElementById('audio-replace-btn')?.addEventListener('click', function () {
        fileInput.click();
    });

    document.getElementById('audio-cancel-btn')?.addEventListener('click', function () {
        resetToOriginalState();
    });

    document.getElementById('audio-delete-btn')?.addEventListener('click', function () {
        openDeleteModal();
    });

    document.getElementById('generate-slang-audio-btn')?.addEventListener('click', async function () {
        const section = document.getElementById('audio-upload-section');
        const slangId = section?.dataset.slangId;
        const koreanInput = document.getElementById('korean');

        if (!slangId) {
            showFormToast('저장된 슬랭에서만 mp3를 생성할 수 있습니다.', 'error');
            return;
        }

        if (!koreanInput?.value.trim()) {
            showFormToast('한국어 욕을 입력한 뒤 다시 시도해주세요.', 'error');
            koreanInput?.focus();
            return;
        }

        setButtonLoading(this, true);
        clearError();

        try {
            const response = await fetch(`/admin/slangs/${slangId}/generate-audio`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    text: koreanInput.value.trim(),
                }),
            });

            const result = await response.json().catch(() => null);

            if (!response.ok || !result?.success) {
                throw new Error(result?.message ?? '슬랭 mp3 생성에 실패했습니다.');
            }

            showExistingStoredAudio(result.result?.audio_url ?? '');
            showFormToast(result.message ?? '슬랭 mp3 생성이 완료되었습니다.');
        } catch (error) {
            showFormToast(error.message ?? '슬랭 mp3 생성에 실패했습니다.', 'error');
        } finally {
            setButtonLoading(this, false);
        }
    });

    initDeleteModal();

    function handleFileSelect(file) {
        clearError();

        const extension = `.${file.name.split('.').pop().toLowerCase()}`;
        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            showError('mp3 파일만 업로드 가능합니다.');
            return;
        }

        if (!ALLOWED_TYPES.includes(file.type) && file.type !== '') {
            showError('mp3 파일만 업로드 가능합니다.');
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            showError('파일 크기는 5MB 이하여야 합니다.');
            return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        showPreviewState(file);
    }

    function showPreviewState(file) {
        document.getElementById('audio-dropzone')?.classList.add('hidden');
        document.getElementById('audio-existing')?.classList.add('hidden');
        document.getElementById('audio-preview')?.classList.remove('hidden');

        document.getElementById('audio-filename').textContent = file.name;
        document.getElementById('audio-filesize').textContent = formatFileSize(file.size);

        const player = document.getElementById('audio-preview-player');
        const objectUrl = URL.createObjectURL(file);
        player.src = objectUrl;
        player.onloadeddata = function () {
            URL.revokeObjectURL(objectUrl);
        };
    }

    function resetToOriginalState() {
        fileInput.value = '';
        document.getElementById('audio-preview')?.classList.add('hidden');

        const previewPlayer = document.getElementById('audio-preview-player');
        if (previewPlayer) {
            previewPlayer.pause();
            previewPlayer.src = '';
        }

        const existingSection = document.getElementById('audio-existing');
        if (existingSection && existingSection.dataset.hasFile === 'true') {
            existingSection.classList.remove('hidden');
            document.getElementById('audio-existing-player')?.classList.remove('hidden');
        } else {
            document.getElementById('audio-dropzone')?.classList.remove('hidden');
        }

        clearError();
    }

    function showDropzoneState() {
        document.getElementById('audio-dropzone')?.classList.remove('hidden');
        document.getElementById('audio-preview')?.classList.add('hidden');
        document.getElementById('audio-existing')?.classList.add('hidden');
        fileInput.value = '';

        const existingPlayer = document.getElementById('audio-existing-player');
        if (existingPlayer) {
            existingPlayer.pause();
            existingPlayer.src = '';
            existingPlayer.classList.add('hidden');
        }
    }

    function showExistingStoredAudio(audioUrl) {
        if (!audioUrl) {
            throw new Error('생성된 오디오 URL을 찾지 못했습니다.');
        }

        document.getElementById('audio-dropzone')?.classList.add('hidden');
        document.getElementById('audio-preview')?.classList.add('hidden');

        const previewPlayer = document.getElementById('audio-preview-player');
        if (previewPlayer) {
            previewPlayer.pause();
            previewPlayer.src = '';
        }

        const existingSection = document.getElementById('audio-existing');
        const existingPlayer = document.getElementById('audio-existing-player');

        if (existingSection) {
            existingSection.dataset.hasFile = 'true';
            existingSection.classList.remove('hidden');
        }

        if (existingPlayer) {
            existingPlayer.src = audioUrl;
            existingPlayer.classList.remove('hidden');
            existingPlayer.load();
        }

        fileInput.value = '';
    }

    function initDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (!modal) {
            return;
        }

        document.getElementById('audio-delete-modal-overlay')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-modal-close')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-modal-cancel')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-confirm')?.addEventListener('click', performAudioDelete);
    }

    function openDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function performAudioDelete() {
        const section = document.getElementById('audio-upload-section');
        const slangId = section?.dataset.slangId;

        if (!slangId) {
            return;
        }

        const confirmButton = document.getElementById('audio-delete-confirm');
        confirmButton.disabled = true;
        confirmButton.textContent = '삭제 중...';

        fetch(`/admin/slangs/${slangId}/audio`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('삭제 실패');
                }

                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    closeDeleteModal();

                    const existingPlayer = document.getElementById('audio-existing-player');
                    if (existingPlayer) {
                        existingPlayer.pause();
                        existingPlayer.src = '';
                    }

                    const existingSection = document.getElementById('audio-existing');
                    if (existingSection) {
                        existingSection.dataset.hasFile = 'false';
                    }

                    showDropzoneState();
                    showFormToast('음성 파일이 삭제되었습니다.');
                }
            })
            .catch(() => {
                showFormToast('음성 파일 삭제에 실패했습니다.', 'error');
            })
            .finally(() => {
                confirmButton.disabled = false;
                confirmButton.textContent = '삭제';
            });
    }

    function showError(message) {
        const errorElement = document.getElementById('audio-error');
        const messageElement = document.getElementById('audio-error-message');

        if (errorElement && messageElement) {
            errorElement.classList.remove('hidden');
            messageElement.textContent = message;
        }
    }

    function clearError() {
        const errorElement = document.getElementById('audio-error');
        const messageElement = document.getElementById('audio-error-message');

        if (errorElement && messageElement) {
            errorElement.classList.add('hidden');
            messageElement.textContent = '';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) {
            return '0 Bytes';
        }

        const kilobyte = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const index = Math.floor(Math.log(bytes) / Math.log(kilobyte));

        return `${parseFloat((bytes / Math.pow(kilobyte, index)).toFixed(1))} ${sizes[index]}`;
    }
})();
