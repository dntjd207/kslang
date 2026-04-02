<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogPostService
{
    public function create(array $data): BlogPost
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->preparePayload($data);

            $blogPost = BlogPost::create($payload);
            $blogPost->slangs()->sync($data['related_slang_ids'] ?? []);

            return $blogPost->load('slangs');
        });
    }

    public function update(BlogPost $blogPost, array $data): BlogPost
    {
        return DB::transaction(function () use ($blogPost, $data) {
            $payload = $this->preparePayload($data, $blogPost);

            $blogPost->update($payload);
            $blogPost->slangs()->sync($data['related_slang_ids'] ?? []);

            return $blogPost->refresh()->load('slangs');
        });
    }

    public function autosave(array $data, ?BlogPost $blogPost = null): BlogPost
    {
        if ($blogPost?->isPublished()) {
            throw ValidationException::withMessages([
                'blog_post_id' => '발행된 글은 자동 임시저장을 지원하지 않습니다. 수동 저장 후 다시 발행해주세요.',
            ]);
        }

        return DB::transaction(function () use ($data, $blogPost) {
            $payload = $this->preparePayload(
                array_merge($data, ['save_action' => 'draft']),
                $blogPost,
                true
            );

            if ($blogPost === null) {
                $blogPost = BlogPost::create($payload);
            } else {
                $blogPost->update($payload);
            }

            $blogPost->slangs()->sync($data['related_slang_ids'] ?? []);

            return $blogPost->refresh()->load('slangs');
        });
    }

    public function delete(BlogPost $blogPost): void
    {
        DB::transaction(function () use ($blogPost): void {
            $blogPost->slangs()->detach();
            $blogPost->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?BlogPost $existing = null, bool $isAutoSave = false): array
    {
        $sourceFields = $this->normalizeSourceFields($data);
        $englishFields = $this->normalizeEnglishFields($data);
        $sourceChanged = $existing !== null && $this->hasSourceChanges($existing, $sourceFields);
        $englishChanged = $existing !== null && $this->hasEnglishChanges($existing, $englishFields);
        $hasEnglishContent = $this->hasMeaningfulContent($englishFields);
        $translationStatus = $this->resolveTranslationStatus($existing, $sourceChanged, $englishChanged, $hasEnglishContent);
        $status = $this->resolveStatus((string) ($data['save_action'] ?? 'draft'));

        $this->guardPublishRequirements($status, $sourceFields, $englishFields, $translationStatus);

        $now = now();
        $translationModel = $hasEnglishContent
            ? $this->normalizeNullableString(
                $data['translation_model'] ?? $existing?->translation_model ?? config('services.gemini.translation_model')
            )
            : null;

        return [
            'slug' => $this->generateUniqueSlug(
                $data['slug'] ?? $englishFields['title_en'] ?? $sourceFields['primary_keyword'] ?? $sourceFields['title_ko'],
                $existing?->id
            ),
            'status' => $status,
            'translation_status' => $translationStatus,
            'category_name' => $this->normalizeNullableString($data['category_name'] ?? null),
            'tag_names' => $this->normalizeTagNames($data['tag_names'] ?? null),
            'search_intent' => $sourceFields['search_intent'],
            'primary_keyword' => $sourceFields['primary_keyword'],
            'content_brief_ko' => $sourceFields['content_brief_ko'],
            'title_ko' => $sourceFields['title_ko'],
            'excerpt_ko' => $sourceFields['excerpt_ko'],
            'body_ko' => $sourceFields['body_ko'],
            'title_en' => $englishFields['title_en'],
            'excerpt_en' => $englishFields['excerpt_en'],
            'body_en' => $englishFields['body_en'],
            'seo_title_en' => $englishFields['seo_title_en'],
            'seo_description_en' => $englishFields['seo_description_en'],
            'canonical_url' => $this->normalizeNullableString($data['canonical_url'] ?? null),
            'translation_model' => $translationModel,
            'last_ko_updated_at' => $this->resolveLastKoUpdatedAt($existing, $sourceFields, $sourceChanged, $now),
            'en_synced_at' => $this->resolveEnglishSyncedAt($existing, $translationStatus, $englishChanged, $hasEnglishContent, $now),
            'last_auto_saved_at' => $isAutoSave ? $now : $existing?->last_auto_saved_at,
            'published_at' => $status === BlogPost::STATUS_PUBLISHED
                ? ($existing?->published_at ?? $now)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    private function normalizeSourceFields(array $data): array
    {
        return [
            'search_intent' => $this->normalizeNullableString($data['search_intent'] ?? null),
            'primary_keyword' => $this->normalizeNullableString($data['primary_keyword'] ?? null),
            'content_brief_ko' => $this->normalizeNullableString($data['content_brief_ko'] ?? null),
            'title_ko' => $this->normalizeNullableString($data['title_ko'] ?? null),
            'excerpt_ko' => $this->normalizeNullableString($data['excerpt_ko'] ?? null),
            'body_ko' => $this->cleanHtml($data['body_ko'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, ?string>
     */
    private function normalizeEnglishFields(array $data): array
    {
        return [
            'title_en' => $this->normalizeNullableString($data['title_en'] ?? null),
            'excerpt_en' => $this->normalizeNullableString($data['excerpt_en'] ?? null),
            'body_en' => $this->cleanHtml($data['body_en'] ?? null),
            'seo_title_en' => $this->normalizeNullableString($data['seo_title_en'] ?? null),
            'seo_description_en' => $this->normalizeNullableString($data['seo_description_en'] ?? null),
        ];
    }

    /**
     * @param  array<string, ?string>  $fields
     */
    private function hasMeaningfulContent(array $fields): bool
    {
        return collect($fields)
            ->contains(fn (?string $value) => trim((string) $value) !== '');
    }

    /**
     * @param  array<string, ?string>  $sourceFields
     */
    private function hasSourceChanges(BlogPost $blogPost, array $sourceFields): bool
    {
        return [
            'search_intent' => $this->normalizeNullableString($blogPost->search_intent),
            'primary_keyword' => $this->normalizeNullableString($blogPost->primary_keyword),
            'content_brief_ko' => $this->normalizeNullableString($blogPost->content_brief_ko),
            'title_ko' => $this->normalizeNullableString($blogPost->title_ko),
            'excerpt_ko' => $this->normalizeNullableString($blogPost->excerpt_ko),
            'body_ko' => $this->cleanHtml($blogPost->body_ko),
        ] !== $sourceFields;
    }

    /**
     * @param  array<string, ?string>  $englishFields
     */
    private function hasEnglishChanges(BlogPost $blogPost, array $englishFields): bool
    {
        return [
            'title_en' => $this->normalizeNullableString($blogPost->title_en),
            'excerpt_en' => $this->normalizeNullableString($blogPost->excerpt_en),
            'body_en' => $this->cleanHtml($blogPost->body_en),
            'seo_title_en' => $this->normalizeNullableString($blogPost->seo_title_en),
            'seo_description_en' => $this->normalizeNullableString($blogPost->seo_description_en),
        ] !== $englishFields;
    }

    private function resolveTranslationStatus(
        ?BlogPost $existing,
        bool $sourceChanged,
        bool $englishChanged,
        bool $hasEnglishContent
    ): string {
        if (! $hasEnglishContent) {
            return BlogPost::TRANSLATION_NONE;
        }

        if ($existing === null || $englishChanged) {
            return BlogPost::TRANSLATION_SYNCED;
        }

        if ($sourceChanged) {
            return BlogPost::TRANSLATION_OUTDATED;
        }

        return $existing->translation_status ?: BlogPost::TRANSLATION_SYNCED;
    }

    private function resolveStatus(string $saveAction): string
    {
        return match ($saveAction) {
            'publish' => BlogPost::STATUS_PUBLISHED,
            'archive' => BlogPost::STATUS_ARCHIVED,
            default => BlogPost::STATUS_DRAFT,
        };
    }

    /**
     * @param  array<string, ?string>  $sourceFields
     * @param  array<string, ?string>  $englishFields
     */
    private function guardPublishRequirements(string $status, array $sourceFields, array $englishFields, string $translationStatus): void
    {
        if ($status !== BlogPost::STATUS_PUBLISHED) {
            return;
        }

        $errors = [];

        if ($sourceFields['title_ko'] === null || $sourceFields['body_ko'] === null) {
            $errors['title_ko'] = '발행하려면 한국어 제목과 본문을 먼저 작성해주세요.';
        }

        if ($englishFields['title_en'] === null || $englishFields['body_en'] === null) {
            $errors['title_en'] = '발행하려면 최신 영어 제목과 본문이 필요합니다.';
        }

        if ($translationStatus !== BlogPost::TRANSLATION_SYNCED) {
            $errors['body_en'] = '한국어 원본이 변경되었습니다. 영어 번역을 다시 생성하거나 직접 수정한 뒤 발행해주세요.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, ?string>  $sourceFields
     */
    private function resolveLastKoUpdatedAt(
        ?BlogPost $existing,
        array $sourceFields,
        bool $sourceChanged,
        Carbon $now
    ): ?Carbon {
        if ($existing === null) {
            return $this->hasMeaningfulContent($sourceFields) ? $now : null;
        }

        return $sourceChanged ? $now : $existing->last_ko_updated_at;
    }

    private function resolveEnglishSyncedAt(
        ?BlogPost $existing,
        string $translationStatus,
        bool $englishChanged,
        bool $hasEnglishContent,
        Carbon $now
    ): ?Carbon {
        if (! $hasEnglishContent) {
            return null;
        }

        if ($existing === null) {
            return $translationStatus === BlogPost::TRANSLATION_SYNCED ? $now : null;
        }

        if ($translationStatus === BlogPost::TRANSLATION_SYNCED && $englishChanged) {
            return $now;
        }

        if ($translationStatus === BlogPost::TRANSLATION_SYNCED && $existing->en_synced_at === null) {
            return $now;
        }

        return $existing->en_synced_at;
    }

    private function generateUniqueSlug(mixed $seed, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim((string) $seed));

        if ($base === '') {
            $base = 'blog-post';
        }

        $candidate = $base;
        $suffix = 2;

        while (
            BlogPost::query()
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function cleanHtml(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return clean($normalized);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeTagNames(mixed $value): ?string
    {
        $tags = collect(preg_split('/[,\\n]/', (string) $value) ?: [])
            ->map(fn (string $tag) => trim($tag))
            ->filter(fn (string $tag) => $tag !== '')
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->values();

        if ($tags->isEmpty()) {
            return null;
        }

        return $tags->implode(', ');
    }
}
