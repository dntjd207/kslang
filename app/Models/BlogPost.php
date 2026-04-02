<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const TRANSLATION_NONE = 'none';

    public const TRANSLATION_SYNCED = 'synced';

    public const TRANSLATION_OUTDATED = 'outdated';

    /** @var list<string> */
    public const SEARCH_INTENTS = [
        'meaning',
        'usage',
        'comparison',
        'warning',
        'listicle',
        'culture-context',
    ];

    protected $fillable = [
        'slug',
        'status',
        'translation_status',
        'category_name',
        'tag_names',
        'search_intent',
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
        'canonical_url',
        'translation_model',
        'last_ko_updated_at',
        'en_synced_at',
        'last_auto_saved_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'last_ko_updated_at' => 'datetime',
            'en_synced_at' => 'datetime',
            'last_auto_saved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    public function slangs(): BelongsToMany
    {
        return $this->belongsToMany(Slang::class)
            ->withTimestamps();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => '임시 저장',
            self::STATUS_PUBLISHED => '발행됨',
            self::STATUS_ARCHIVED => '보관됨',
            default => '알 수 없음',
        };
    }

    public function getTranslationStatusLabelAttribute(): string
    {
        return match ($this->translation_status) {
            self::TRANSLATION_NONE => '영문 없음',
            self::TRANSLATION_SYNCED => '영문 최신',
            self::TRANSLATION_OUTDATED => '재번역 필요',
            default => '알 수 없음',
        };
    }

    public function getPublicTitleAttribute(): string
    {
        return trim((string) ($this->title_en ?: $this->title_ko));
    }

    public function getPublicExcerptAttribute(): ?string
    {
        $excerpt = trim((string) ($this->excerpt_en ?: $this->excerpt_ko));

        return $excerpt !== '' ? $excerpt : null;
    }

    public function getResolvedSeoTitleAttribute(): string
    {
        $seoTitle = trim((string) $this->seo_title_en);

        if ($seoTitle !== '') {
            return $seoTitle;
        }

        return Str::limit($this->public_title, 60, '');
    }

    public function getResolvedSeoDescriptionAttribute(): ?string
    {
        $description = trim((string) $this->seo_description_en);

        if ($description !== '') {
            return $description;
        }

        if ($this->public_excerpt !== null) {
            return $this->public_excerpt;
        }

        $plainText = trim(strip_tags((string) $this->body_en));

        return $plainText !== '' ? Str::limit($plainText, 160) : null;
    }

    /**
     * @return list<string>
     */
    public function getTagsListAttribute(): array
    {
        return collect(explode(',', (string) $this->tag_names))
            ->map(fn (string $tag) => trim($tag))
            ->filter(fn (string $tag) => $tag !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function getReadingTimeMinutesAttribute(): int
    {
        $plainText = trim(strip_tags((string) ($this->body_en ?: $this->body_ko)));
        $wordCount = str_word_count($plainText);

        return max(1, (int) ceil($wordCount / 220));
    }
}
