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
            'advlist', 'autolink', 'lists', 'link', 'image', 'table',
            'code', 'fullscreen', 'wordcount',
            'searchreplace', 'preview', 'visualblocks',
        ],
        toolbar: [
            'undo redo | blocks fontsize | bold italic underline | forecolor backcolor | alignleft aligncenter alignright |',
            'bullist numlist blockquote | link image table | removeformat | searchreplace visualblocks | code preview fullscreen',
        ],
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        images_upload_handler: function (blobInfo) {
            return new Promise(function (resolve, reject) {
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                fetch(`{{ route('admin.blog-posts.upload-image') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (err) {
                            reject(err.message || '이미지 업로드에 실패했습니다.');
                        });
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.location) {
                        resolve(data.location);
                    }
                })
                .catch(function () {
                    reject('이미지 업로드 중 오류가 발생했습니다.');
                });
            });
        },
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
            img { max-width: 100%; height: auto; border-radius: 8px; margin: 1em 0; }
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

        scheduleSeoCheck();
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

    // === SEO 실시간 체크리스트 ===
    const seoChecklist = document.getElementById('seo-checklist');
    const seoScoreBadge = document.getElementById('seo-score-badge');
    let seoDebounceTimer = null;

    function stripHtml(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        return tmp.textContent || '';
    }

    function countWords(text) {
        return text.trim().split(/\s+/).filter(Boolean).length;
    }

    function getHeadings(html, tag) {
        const re = new RegExp(`<${tag}[^>]*>`, 'gi');
        return (html || '').match(re) || [];
    }

    function containsKeyword(text, keyword) {
        if (!keyword) {
            return false;
        }
        return text.toLowerCase().includes(keyword.toLowerCase());
    }

    function getVal(id) {
        return (document.getElementById(id)?.value || '').trim();
    }

    function getEditorHtml(id) {
        const editor = tinymce.get(id);
        if (editor) {
            return editor.getContent() || '';
        }
        return (document.getElementById(id)?.value || '');
    }

    function checkedSlangCount() {
        return form.querySelectorAll('input[name="related_slang_ids[]"]:checked').length;
    }

    function countInternalLinks(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        return tmp.querySelectorAll('a[href]').length;
    }

    function hasH1InBody(html) {
        return /<h1[\s>]/i.test(html || '');
    }

    function parseImages(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        const imgs = tmp.querySelectorAll('img');
        const result = [];

        imgs.forEach(function (img) {
            const src = img.getAttribute('src') || '';
            const alt = (img.getAttribute('alt') || '').trim();
            const parts = src.split(/[?#]/)[0].split('/');
            const filename = parts[parts.length - 1] || src;

            result.push({ src: src, filename: filename, alt: alt, hasAlt: alt.length > 0 });
        });

        return result;
    }

    function runSeoChecks() {
        const keyword = getVal('primary_keyword');
        const titleEn = getVal('title_en');
        const excerptEn = getVal('excerpt_en');
        const bodyEnHtml = getEditorHtml('body_en_editor');
        const bodyEnText = stripHtml(bodyEnHtml);
        const bodyKoHtml = getEditorHtml('body_ko_editor');
        const seoTitle = getVal('seo_title_en');
        const seoDesc = getVal('seo_description_en');
        const slug = getVal('slug');
        const titleKo = getVal('title_ko');
        const excerptKo = getVal('excerpt_ko');
        const categoryName = getVal('category_name');
        const tagNames = getVal('tag_names');

        const bodyEnWords = countWords(bodyEnText);
        const h2CountEn = getHeadings(bodyEnHtml, 'h2').length;
        const h2CountKo = getHeadings(bodyKoHtml, 'h3').length + getHeadings(bodyKoHtml, 'h2').length;
        const linksInBody = countInternalLinks(bodyEnHtml);

        const checks = [];

        checks.push({ group: '전략', label: '핵심 키워드 입력됨', pass: keyword.length > 0 });
        checks.push({ group: '전략', label: '검색 의도 선택됨', pass: getVal('search_intent') !== '' });
        checks.push({ group: '전략', label: '카테고리 지정됨', pass: categoryName.length > 0 });
        checks.push({ group: '전략', label: '태그 1개 이상', pass: parseTagNames(tagNames).length >= 1 });

        checks.push({ group: '한국어', label: '한국어 제목 있음', pass: titleKo.length > 0 });
        checks.push({ group: '한국어', label: '한국어 요약 있음', pass: excerptKo.length > 0 });
        checks.push({ group: '한국어', label: '한국어 본문 있음', pass: stripHtml(bodyKoHtml).length > 50 });
        checks.push({ group: '한국어', label: '한국어 본문 H2/H3 구조 사용', pass: h2CountKo >= 2 });

        checks.push({ group: '영어 본문', label: '영어 제목 있음', pass: titleEn.length > 0 });
        checks.push({ group: '영어 본문', label: '영어 제목 60자 이내', pass: titleEn.length > 0 && titleEn.length <= 65, detail: titleEn.length > 0 ? `${titleEn.length}자` : '' });
        checks.push({ group: '영어 본문', label: '영어 요약 있음', pass: excerptEn.length > 0 });
        checks.push({ group: '영어 본문', label: '영어 본문 800단어 이상', pass: bodyEnWords >= 800, detail: `${bodyEnWords}단어` });
        checks.push({ group: '영어 본문', label: '영어 본문 H2 3개 이상', pass: h2CountEn >= 3, detail: `${h2CountEn}개` });
        checks.push({ group: '영어 본문', label: '본문에 H1 미사용', pass: !hasH1InBody(bodyEnHtml) });
        checks.push({ group: '영어 본문', label: '본문에 링크 포함', pass: linksInBody >= 1, detail: `${linksInBody}개` });

        checks.push({ group: '키워드', label: '키워드 → 영어 제목에 포함', pass: containsKeyword(titleEn, keyword) });
        checks.push({ group: '키워드', label: '키워드 → 영어 요약에 포함', pass: containsKeyword(excerptEn, keyword) });
        checks.push({ group: '키워드', label: '키워드 → 영어 본문에 포함', pass: containsKeyword(bodyEnText, keyword) });
        checks.push({ group: '키워드', label: '키워드 → SEO 제목에 포함', pass: containsKeyword(seoTitle, keyword) });
        checks.push({ group: '키워드', label: '키워드 → 슬러그에 포함', pass: containsKeyword(slug.replace(/-/g, ' '), keyword) });

        checks.push({ group: 'SEO 메타', label: 'SEO 제목 입력됨', pass: seoTitle.length > 0 });
        checks.push({ group: 'SEO 메타', label: 'SEO 제목 50~60자', pass: seoTitle.length >= 50 && seoTitle.length <= 65, detail: seoTitle.length > 0 ? `${seoTitle.length}자` : '' });
        checks.push({ group: 'SEO 메타', label: 'SEO 설명 입력됨', pass: seoDesc.length > 0 });
        checks.push({ group: 'SEO 메타', label: 'SEO 설명 140~160자', pass: seoDesc.length >= 130 && seoDesc.length <= 165, detail: seoDesc.length > 0 ? `${seoDesc.length}자` : '' });
        checks.push({ group: 'SEO 메타', label: '슬러그 입력됨', pass: slug.length > 0 });

        checks.push({ group: '연결', label: '관련 슬랭 1개 이상 연결', pass: checkedSlangCount() >= 1, detail: `${checkedSlangCount()}개` });

        const imagesEn = parseImages(bodyEnHtml);
        const imagesKo = parseImages(bodyKoHtml);
        const allImages = [
            ...imagesEn.map(i => ({ ...i, source: 'EN' })),
            ...imagesKo.map(i => ({ ...i, source: 'KO' })),
        ];
        const imagesWithoutAlt = allImages.filter(i => !i.hasAlt);

        if (allImages.length > 0) {
            checks.push({ group: '이미지', label: '모든 이미지에 alt 텍스트 있음', pass: imagesWithoutAlt.length === 0, detail: `${allImages.length - imagesWithoutAlt.length}/${allImages.length}` });
        }

        renderSeoChecklist(checks, allImages);
    }

    function renderSeoChecklist(checks, images) {
        const passCount = checks.filter(c => c.pass).length;
        const total = checks.length;
        const pct = total > 0 ? Math.round((passCount / total) * 100) : 0;

        seoScoreBadge.textContent = `${passCount}/${total}`;
        seoScoreBadge.className = 'rounded-full px-2.5 py-0.5 text-xs font-bold tabular-nums ';
        if (pct >= 85) {
            seoScoreBadge.classList.add('bg-emerald-100', 'text-emerald-700');
        } else if (pct >= 55) {
            seoScoreBadge.classList.add('bg-amber-100', 'text-amber-700');
        } else {
            seoScoreBadge.classList.add('bg-red-100', 'text-red-700');
        }

        let html = '';
        let currentGroup = '';

        checks.forEach(function (c) {
            if (c.group !== currentGroup) {
                currentGroup = c.group;
                html += `<p class="mt-2 mb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 first:mt-0">${escapeHtml(currentGroup)}</p>`;
            }

            const icon = c.pass
                ? '<svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

            const textClass = c.pass ? 'text-gray-500' : 'text-gray-800 font-medium';
            const detail = c.detail ? ` <span class="text-gray-400">(${escapeHtml(c.detail)})</span>` : '';

            html += `<div class="flex items-center gap-2 py-0.5 ${textClass}">${icon}<span>${escapeHtml(c.label)}${detail}</span></div>`;
        });

        if (images && images.length > 0) {
            html += '<div class="mt-3 rounded-lg border border-gray-200 bg-gray-50/70 p-3">';
            html += '<p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">이미지 상세</p>';

            images.forEach(function (img, idx) {
                const altIcon = img.hasAlt
                    ? '<svg class="h-3 w-3 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg class="h-3 w-3 shrink-0 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

                const sourceLabel = `<span class="rounded bg-gray-200 px-1 py-0.5 text-[10px] font-semibold text-gray-500">${img.source}</span>`;
                const altText = img.hasAlt
                    ? `<span class="text-gray-400">alt: ${escapeHtml(img.alt)}</span>`
                    : '<span class="font-medium text-red-500">alt 없음</span>';

                html += '<div class="py-1.5' + (idx > 0 ? ' border-t border-gray-200' : '') + '">';
                html += `<div class="flex items-center gap-1.5">${altIcon} ${sourceLabel} <span class="truncate font-mono text-gray-700" title="${escapeHtml(img.filename)}">${escapeHtml(img.filename)}</span></div>`;
                html += `<div class="mt-0.5 pl-[18px] text-[11px]">${altText}</div>`;
                html += '</div>';
            });

            html += '</div>';
        }

        seoChecklist.innerHTML = html;
    }

    function scheduleSeoCheck() {
        window.clearTimeout(seoDebounceTimer);
        seoDebounceTimer = window.setTimeout(runSeoChecks, 600);
    }

    form.addEventListener('change', scheduleSeoCheck);

    window.setTimeout(runSeoChecks, 800);
});
</script>
