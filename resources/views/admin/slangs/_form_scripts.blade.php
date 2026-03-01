// === 예문 동적 추가/삭제 ===
let exampleIndex = document.querySelectorAll('.example-row').length;
const MAX_EXAMPLES = 50;

document.getElementById('add-example')?.addEventListener('click', function () {
    const container = document.getElementById('examples-container');
    const noMsg = document.getElementById('no-examples-msg');
    const currentCount = container.querySelectorAll('.example-row').length;

    if (currentCount >= MAX_EXAMPLES) {
        alert('예문은 최대 ' + MAX_EXAMPLES + '개까지 추가할 수 있습니다.');
        return;
    }

    if (noMsg) noMsg.classList.add('hidden');

    const row = document.createElement('div');
    row.className = 'example-row flex flex-col sm:flex-row items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200';
    row.dataset.index = exampleIndex;
    row.innerHTML = `
        <span class="drag-handle-example cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 shrink-0 sm:mt-7">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
            </svg>
        </span>
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">한국어 예문</label>
            <input type="text"
                   name="examples[${exampleIndex}][korean_example]"
                   placeholder="예: 씨발, 또 늦었어!"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">영어 번역</label>
            <input type="text"
                   name="examples[${exampleIndex}][english_example]"
                   placeholder="예: F**k, I'm late again!"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <button type="button" class="remove-example shrink-0 sm:mt-7 p-1 text-red-400 hover:text-red-600 transition" title="예문 삭제">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(row);
    exampleIndex++;

    row.querySelector('input[type="text"]').focus();

    initExampleSortable();
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-example')) {
        e.target.closest('.example-row').remove();
        reindexExamples();
        const container = document.getElementById('examples-container');
        const noMsg = document.getElementById('no-examples-msg');
        if (container && container.querySelectorAll('.example-row').length === 0 && noMsg) {
            noMsg.classList.remove('hidden');
        }
    }
});

function reindexExamples() {
    document.querySelectorAll('.example-row').forEach((row, index) => {
        row.dataset.index = index;
        row.querySelectorAll('input[name*="korean_example"]').forEach(input => {
            input.name = `examples[${index}][korean_example]`;
        });
        row.querySelectorAll('input[name*="english_example"]').forEach(input => {
            input.name = `examples[${index}][english_example]`;
        });
        const hiddenId = row.querySelector('input[name*="[id]"]');
        if (hiddenId) {
            hiddenId.name = `examples[${index}][id]`;
        }
    });
    exampleIndex = document.querySelectorAll('.example-row').length;
}

// === 예문 드래그 앤 드롭 정렬 ===
function initExampleSortable() {
    const container = document.getElementById('examples-container');
    if (!container) return;

    if (container._sortable) {
        container._sortable.destroy();
    }

    container._sortable = new Sortable(container, {
        handle: '.drag-handle-example',
        animation: 150,
        ghostClass: 'opacity-50',
        chosenClass: 'shadow-lg',
        onEnd: function () {
            reindexExamples();
        }
    });
}

document.addEventListener('DOMContentLoaded', initExampleSortable);

// === 음성 파일 업로드 관리 ===
(function () {
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    const ALLOWED_TYPES = ['audio/mpeg'];
    const ALLOWED_EXTENSIONS = ['.mp3'];

    const fileInput = document.getElementById('audio-file-input');
    const dropArea = document.getElementById('audio-drop-area');
    const selectBtn = document.getElementById('audio-select-btn');

    if (!fileInput || !dropArea) return;

    // 파일 선택 버튼
    selectBtn?.addEventListener('click', function () {
        fileInput.click();
    });

    // 파일 선택 변경
    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    // 드래그 앤 드롭
    dropArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('border-indigo-400', 'bg-indigo-50/50');
    });

    dropArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-indigo-400', 'bg-indigo-50/50');
    });

    dropArea.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-indigo-400', 'bg-indigo-50/50');
        if (e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    // 다른 파일 선택 / 교체 버튼
    document.getElementById('audio-change-btn')?.addEventListener('click', function () {
        fileInput.click();
    });

    document.getElementById('audio-replace-btn')?.addEventListener('click', function () {
        fileInput.click();
    });

    // 취소 버튼
    document.getElementById('audio-cancel-btn')?.addEventListener('click', function () {
        resetToOriginalState();
    });

    // 삭제 버튼 → 모달 열기
    document.getElementById('audio-delete-btn')?.addEventListener('click', function () {
        openDeleteModal();
    });

    initDeleteModal();

    function handleFileSelect(file) {
        clearError();

        const extension = '.' + file.name.split('.').pop().toLowerCase();
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

    // AJAX 삭제 모달
    function initDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (!modal) return;

        document.getElementById('audio-delete-modal-overlay')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-modal-close')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-modal-cancel')?.addEventListener('click', closeDeleteModal);
        document.getElementById('audio-delete-confirm')?.addEventListener('click', performAudioDelete);
    }

    function openDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('audio-delete-modal');
        if (modal) modal.classList.add('hidden');
    }

    function performAudioDelete() {
        const section = document.getElementById('audio-upload-section');
        const slangId = section?.dataset.slangId;
        if (!slangId) return;

        const confirmBtn = document.getElementById('audio-delete-confirm');
        confirmBtn.disabled = true;
        confirmBtn.textContent = '삭제 중...';

        fetch(`/admin/slangs/${slangId}/audio`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('삭제 실패');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                closeDeleteModal();

                const existingPlayer = document.getElementById('audio-existing-player');
                if (existingPlayer) {
                    existingPlayer.pause();
                    existingPlayer.src = '';
                }

                const existingSection = document.getElementById('audio-existing');
                if (existingSection) existingSection.dataset.hasFile = 'false';

                showDropzoneState();
                showToast('음성 파일이 삭제되었습니다.', 'success');
            }
        })
        .catch(() => {
            showToast('음성 파일 삭제에 실패했습니다.', 'error');
        })
        .finally(() => {
            confirmBtn.disabled = false;
            confirmBtn.textContent = '삭제';
        });
    }

    function showError(message) {
        const errorEl = document.getElementById('audio-error');
        const messageEl = document.getElementById('audio-error-message');
        if (errorEl && messageEl) {
            errorEl.classList.remove('hidden');
            messageEl.textContent = message;
        }
    }

    function clearError() {
        const errorEl = document.getElementById('audio-error');
        const messageEl = document.getElementById('audio-error-message');
        if (errorEl && messageEl) {
            errorEl.classList.add('hidden');
            messageEl.textContent = '';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function showToast(message, type) {
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
})();
