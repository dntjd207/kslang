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

function buildExampleRowMarkup(index, example = {}) {
    const hiddenId = example.id
        ? `<input type="hidden" name="examples[${index}][id]" value="${escapeHtml(example.id)}">`
        : '';

    return `
        <span class="drag-handle-example cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 shrink-0 sm:mt-7">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
            </svg>
        </span>
        ${hiddenId}
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">한국어 예문</label>
            <input type="text"
                   name="examples[${index}][korean_example]"
                   value="${escapeHtml(example.korean_example ?? '')}"
                   placeholder="예: 씨발, 또 늦었어!"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">영어 번역</label>
            <input type="text"
                   name="examples[${index}][english_example]"
                   value="${escapeHtml(example.english_example ?? '')}"
                   placeholder="예: F**k, I'm late again!"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <button type="button" class="remove-example shrink-0 sm:mt-7 p-1 text-red-400 hover:text-red-600 transition" title="예문 삭제">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
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
    row.className = 'example-row flex flex-col sm:flex-row items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200';
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

function getRegenerationPayload(section) {
    return {
        section,
        korean: document.getElementById('korean')?.value ?? '',
        pronunciation: document.getElementById('pronunciation')?.value ?? '',
        english_description: document.getElementById('english_description')?.value ?? '',
        korean_description: document.getElementById('korean_description')?.value ?? '',
        level: document.getElementById('level')?.value ?? '',
        usage_frequency: document.getElementById('usage_frequency')?.value ?? '',
        usage_context: document.getElementById('usage_context')?.value ?? '',
        english_usage_context: document.getElementById('english_usage_context')?.value ?? '',
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
    }
}

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
