<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('blog-post-form');
    const feedback = document.getElementById('blog-ai-feedback');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const autosaveEnabled = form?.dataset.autosaveEnabled === 'true';
    const autosaveEndpoint = form?.dataset.autosaveEndpoint ?? '';
    const autosaveStatusText = document.getElementById('autosave-status-text');
    const autosaveLastSavedLabel = document.getElementById('autosave-last-saved-label');
    const autosaveDot = document.getElementById('autosave-dot');
    const blogPostIdInput = document.getElementById('blog_post_id');
    const generateDraftButton = document.getElementById('generate-blog-draft-button');
    const translateButton = document.getElementById('translate-blog-button');
    const categoryNameInput = document.getElementById('category_name');
    const tagNamesInput = document.getElementById('tag_names');
    const tagChipPreview = document.getElementById('tag-chip-preview');
    const titleEnInput = document.getElementById('title_en');
    const slugInput = document.getElementById('slug');
    const seoTitleInput = document.getElementById('seo_title_en');
    const seoDescriptionInput = document.getElementById('seo_description_en');
    const excerptEnInput = document.getElementById('excerpt_en');
    const previewTitle = document.getElementById('seo-preview-title');
    const previewUrl = document.getElementById('seo-preview-url');
    const previewDescription = document.getElementById('seo-preview-description');
    const translationModelInput = document.getElementById('translation_model');
    const statusBadge = document.getElementById('status-badge');
    const translationStatusBadge = document.getElementById('translation-status-badge');
    const translationModelLabel = document.getElementById('translation-model-label');
    const statusBadgeClasses = {
        draft: 'bg-amber-100 text-amber-800',
        published: 'bg-emerald-100 text-emerald-800',
        archived: 'bg-slate-200 text-slate-700',
    };
    const translationBadgeClasses = {
        none: 'bg-slate-100 text-slate-700',
        synced: 'bg-blue-100 text-blue-800',
        outdated: 'bg-rose-100 text-rose-800',
    };
    let autosaveTimer = null;
    let autosaveInFlight = false;
    let lastSavedSignature = '';
    let isDirty = false;

    if (! form || ! csrfToken) {
        return;
    }

    tinymce.init({
        selector: '.blog-rich-editor',
        height: 560,
        menubar: 'edit insert view format table tools help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'table',
            'code', 'fullscreen', 'wordcount',
            'searchreplace', 'preview', 'visualblocks',
        ],
        toolbar: [
            'undo redo | blocks fontsize | bold italic underline | forecolor backcolor | alignleft aligncenter alignright |',
            'bullist numlist blockquote | link table | removeformat | searchreplace visualblocks | code preview fullscreen',
        ],
        font_size_formats: '12px 14px 16px 18px 20px 24px 28px 32px',
        table_advtab: true,
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-size: 16px;
                line-height: 1.7;
                color: #1f2937;
                max-width: 760px;
                margin: 0 auto;
                padding: 24px;
            }
            h2 { font-size: 1.7rem; font-weight: 700; margin: 1.8em 0 0.8em; }
            h3 { font-size: 1.25rem; font-weight: 700; margin: 1.4em 0 0.6em; }
            p { margin: 0 0 1.1em; }
            ul, ol { margin: 0 0 1.1em 1.4em; }
            li { margin-bottom: 0.45em; }
            blockquote {
                border-left: 4px solid #cbd5e1;
                padding-left: 1rem;
                color: #475569;
                margin: 1.4em 0;
            }
            table { border-collapse: collapse; width: 100%; margin-bottom: 1.1em; }
            th, td { border: 1px solid #d1d5db; padding: 0.75rem; }
            th { background: #f8fafc; font-weight: 700; }
            a { color: #4f46e5; text-decoration: underline; }
        `,
        min_height: 560,
        link_default_target: '_blank',
        setup: function (editor) {
            editor.on('change input undo redo', function () {
                editor.save();
                handleDirtyChange();
            });
        }
    });

    form.addEventListener('submit', function () {
        tinymce.triggerSave();
    });

    function setFeedback(message, tone = 'info') {
        if (! feedback) {
            return;
        }

        const classes = {
            info: 'border-blue-200 bg-blue-50 text-blue-800',
            success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            error: 'border-red-200 bg-red-50 text-red-800',
        };

        feedback.className = `rounded-lg border px-3 py-2 text-sm ${classes[tone] ?? classes.info}`;
        feedback.textContent = message;
        feedback.classList.remove('hidden');
    }

    function setAutosaveStatus(message, tone = 'idle') {
        if (!autosaveStatusText || !autosaveDot) {
            return;
        }

        autosaveStatusText.textContent = message;
        autosaveDot.className = 'h-2.5 w-2.5 rounded-full';

        const toneClasses = {
            idle: 'bg-slate-300',
            dirty: 'bg-amber-400',
            saving: 'bg-blue-500 animate-pulse',
            saved: 'bg-emerald-500',
            error: 'bg-red-500',
            disabled: 'bg-slate-400',
        };

        autosaveDot.classList.add(...(toneClasses[tone] ?? toneClasses.idle).split(' '));
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function updateSeoPreview() {
        const title = (seoTitleInput?.value || titleEnInput?.value || 'SEO title preview').trim();
        const slug = (slugInput?.value || 'your-slug').trim();
        const description = (seoDescriptionInput?.value || excerptEnInput?.value || 'SEO description preview').trim();

        if (previewTitle) {
            previewTitle.textContent = title || 'SEO title preview';
        }

        if (previewUrl) {
            previewUrl.textContent = `{{ url('/blog') }}/${slug || 'your-slug'}`;
        }

        if (previewDescription) {
            previewDescription.textContent = description || 'SEO description preview';
        }
    }

    function parseTagNames(value) {
        return String(value || '')
            .split(/,|\n/)
            .map((tag) => tag.trim())
            .filter((tag) => tag !== '');
    }

    function renderTagChips() {
        if (!tagChipPreview || !tagNamesInput) {
            return;
        }

        const tags = parseTagNames(tagNamesInput.value);

        if (tags.length === 0) {
            tagChipPreview.innerHTML = '';
            return;
        }

        tagChipPreview.innerHTML = tags
            .map((tag) => `<span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">${escapeHtml(tag)}</span>`)
            .join('');
    }

    function setFieldValue(id, value) {
        const field = document.getElementById(id);

        if (! field) {
            return;
        }

        field.value = value ?? '';
    }

    function setEditorValue(id, value) {
        const editor = tinymce.get(id);

        if (editor) {
            editor.setContent(value ?? '');
            editor.save();

            return;
        }

        setFieldValue(id, value);
    }

    function applyGeneratedPayload(data) {
        if (typeof data.category_name !== 'undefined') {
            setFieldValue('category_name', data.category_name);
        }

        if (typeof data.tag_names !== 'undefined') {
            setFieldValue('tag_names', data.tag_names);
        }

        if (typeof data.title_ko !== 'undefined') {
            setFieldValue('title_ko', data.title_ko);
        }

        if (typeof data.excerpt_ko !== 'undefined') {
            setFieldValue('excerpt_ko', data.excerpt_ko);
        }

        if (typeof data.body_ko !== 'undefined') {
            setEditorValue('body_ko_editor', data.body_ko);
        }

        if (typeof data.title_en !== 'undefined') {
            setFieldValue('title_en', data.title_en);
        }

        if (typeof data.excerpt_en !== 'undefined') {
            setFieldValue('excerpt_en', data.excerpt_en);
        }

        if (typeof data.body_en !== 'undefined') {
            setEditorValue('body_en_editor', data.body_en);
        }

        if (typeof data.seo_title_en !== 'undefined') {
            setFieldValue('seo_title_en', data.seo_title_en);
        }

        if (typeof data.seo_description_en !== 'undefined') {
            setFieldValue('seo_description_en', data.seo_description_en);
        }

        if (typeof data.translation_model !== 'undefined' && translationModelInput) {
            translationModelInput.value = data.translation_model;
        }

        renderTagChips();
        updateSeoPreview();
        handleDirtyChange();
    }

    function firstErrorMessage(payload) {
        if (payload?.message) {
            return payload.message;
        }

        if (payload?.errors) {
            const firstKey = Object.keys(payload.errors)[0];

            if (firstKey && Array.isArray(payload.errors[firstKey]) && payload.errors[firstKey][0]) {
                return payload.errors[firstKey][0];
            }
        }

        return '요청 처리 중 오류가 발생했습니다.';
    }

    function serializeFormForAutosave() {
        tinymce.triggerSave();

        return JSON.stringify(
            Array.from(new FormData(form).entries())
                .filter(([key]) => !['_token', '_method'].includes(key))
        );
    }

    function hasMeaningfulAutosaveContent() {
        return [
            'category_name',
            'tag_names',
            'primary_keyword',
            'content_brief_ko',
            'title_ko',
            'excerpt_ko',
            'body_ko',
            'title_en',
            'excerpt_en',
            'body_en',
            'seo_title_en',
            'seo_description_en',
        ].some((id) => {
            const field = document.getElementById(id);

            return field && String(field.value || '').trim() !== '';
        });
    }

    function ensureUpdateMode(data) {
        if (!data?.blog_post_id) {
            return;
        }

        blogPostIdInput.value = data.blog_post_id;
        form.action = data.update_url || form.action;

        let methodInput = form.querySelector('input[name="_method"]');

        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }

        methodInput.value = 'PUT';

        if (data.edit_url && window.location.href !== data.edit_url) {
            window.history.replaceState({}, '', data.edit_url);
        }
    }

    function updateStatusBadges(data) {
        if (statusBadge && data?.status) {
            statusBadge.className = `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadgeClasses[data.status] ?? 'bg-gray-100 text-gray-700'}`;
            statusBadge.textContent = data.status_label || statusBadge.textContent;
        }

        if (translationStatusBadge && data?.translation_status) {
            translationStatusBadge.className = `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${translationBadgeClasses[data.translation_status] ?? 'bg-gray-100 text-gray-700'}`;
            translationStatusBadge.textContent = data.translation_status_label || translationStatusBadge.textContent;
        }

        if (translationModelLabel && typeof data?.translation_model !== 'undefined') {
            translationModelLabel.textContent = data.translation_model || '-';
        }
    }

    function scheduleAutoSave() {
        if (!autosaveEnabled) {
            return;
        }

        if (!hasMeaningfulAutosaveContent()) {
            setAutosaveStatus('자동 임시저장 대기 중', 'idle');
            return;
        }

        window.clearTimeout(autosaveTimer);
        autosaveTimer = window.setTimeout(() => {
            saveAutoDraft();
        }, 8000);
    }

    async function saveAutoDraft() {
        if (!autosaveEnabled || autosaveInFlight) {
            return;
        }

        if (!hasMeaningfulAutosaveContent()) {
            return;
        }

        const currentSignature = serializeFormForAutosave();

        if (currentSignature === lastSavedSignature) {
            isDirty = false;
            setAutosaveStatus('자동 임시저장 대기 중', 'idle');
            return;
        }

        autosaveInFlight = true;
        setAutosaveStatus('자동 임시저장 중...', 'saving');

        try {
            const autosaveData = new FormData(form);
            autosaveData.delete('_method');

            const response = await fetch(autosaveEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: autosaveData,
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(firstErrorMessage(payload));
            }

            ensureUpdateMode(payload.data || {});

            if (slugInput && typeof payload.data?.slug !== 'undefined') {
                slugInput.value = payload.data.slug || '';
            }

            if (categoryNameInput && typeof payload.data?.category_name !== 'undefined') {
                categoryNameInput.value = payload.data.category_name || '';
            }

            if (tagNamesInput && typeof payload.data?.tag_names !== 'undefined') {
                tagNamesInput.value = payload.data.tag_names || '';
            }

            if (translationModelInput && typeof payload.data?.translation_model !== 'undefined') {
                translationModelInput.value = payload.data.translation_model || '';
            }

            updateStatusBadges(payload.data || {});
            renderTagChips();
            updateSeoPreview();
            lastSavedSignature = serializeFormForAutosave();
            isDirty = false;

            if (autosaveLastSavedLabel) {
                autosaveLastSavedLabel.innerHTML = `마지막 자동 저장: <span class="font-medium text-gray-800">${payload.data?.last_auto_saved_at || '-'}</span>`;
            }

            setAutosaveStatus('자동 임시저장 완료', 'saved');
        } catch (error) {
            setAutosaveStatus('자동 임시저장 실패', 'error');
        } finally {
            autosaveInFlight = false;
        }
    }

    function handleDirtyChange() {
        isDirty = true;

        if (autosaveEnabled) {
            setAutosaveStatus('변경사항 감지됨', 'dirty');
            scheduleAutoSave();
        }
    }

    async function runAiAction(endpoint, button) {
        tinymce.triggerSave();

        const originalText = button.textContent.trim();
        const loadingText = button.dataset.loadingText || originalText;

        button.disabled = true;
        button.textContent = loadingText;
        setFeedback('AI 요청을 처리하고 있습니다...', 'info');

        try {
            const formData = new FormData(form);
            formData.delete('_method');

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await response.json();

            if (! response.ok || ! payload.success) {
                throw new Error(firstErrorMessage(payload));
            }

            applyGeneratedPayload(payload.data || {});
            setFeedback(payload.message || '완료되었습니다.', 'success');
        } catch (error) {
            setFeedback(error.message || '요청 처리 중 오류가 발생했습니다.', 'error');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    generateDraftButton?.addEventListener('click', function () {
        runAiAction(`{{ route('admin.blog-posts.generate-draft') }}`, generateDraftButton);
    });

    translateButton?.addEventListener('click', function () {
        runAiAction(`{{ route('admin.blog-posts.translate') }}`, translateButton);
    });

    form.addEventListener('input', function (event) {
        if (event.target.closest('button')) {
            return;
        }

        handleDirtyChange();
    });

    form.addEventListener('change', function () {
        handleDirtyChange();
    });

    [
        'category_name',
        'tag_names',
        'title_en',
        'slug',
        'seo_title_en',
        'seo_description_en',
        'excerpt_en',
    ].forEach(function (id) {
        document.getElementById(id)?.addEventListener('input', updateSeoPreview);
    });

    tagNamesInput?.addEventListener('input', renderTagChips);

    if (!autosaveEnabled) {
        setAutosaveStatus('발행된 글은 자동 서버 저장 비활성화', 'disabled');
    }

    window.setTimeout(function () {
        lastSavedSignature = serializeFormForAutosave();
    }, 400);

    renderTagChips();
    updateSeoPreview();
});
</script>
