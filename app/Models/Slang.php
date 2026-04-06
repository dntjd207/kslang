<?php

namespace App\Models;

use App\Services\AudioFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Slang extends Model
{
    public const STATUS_COMPLETE = 'complete';

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'korean',
        'ai_generation_hint',
        'pronunciation',
        'english_description',
        'korean_description',
        'level',
        'usage_frequency',
        'usage_context',
        'english_usage_context',
        'audio_file',
        'audio_disk',
        'sort_order',
        'is_active',
        'content_status',
        'is_new',
        'approved_at',
        'thread_post_formats',
        'thread_post_generated_at',
        'public_slug',
        'public_title_en',
        'public_summary_en',
        'seo_title_en',
        'seo_description_en',
        'seo_keywords_en',
        'faq_items',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'approved_at' => 'datetime',
            'thread_post_formats' => 'array',
            'thread_post_generated_at' => 'datetime',
            'faq_items' => 'array',
        ];
    }

    /**
     * API에 노출 가능한 슬랭만 조회하는 스코프.
     * content_status가 'complete' 또는 'approved'이고, is_active가 true인 것만 반환.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeApiVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereIn('content_status', [self::STATUS_COMPLETE, self::STATUS_APPROVED]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->apiVisible()
            ->whereNotNull('public_slug')
            ->where('public_slug', '!=', '');
    }

    /**
     * 앱 API 목록용 정렬 스코프.
     * 신규 단어를 먼저 보여주고, 신규 단어끼리는 승인일이 빠른 순으로 정렬한다.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrderForApiFeed(Builder $query): Builder
    {
        return $query->orderByDesc('is_new')
            ->orderByRaw('case when is_new = 1 then approved_at end asc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function needsAutoFill(): bool
    {
        return $this->content_status === self::STATUS_PENDING;
    }

    public function getContentStatusLabelAttribute(): string
    {
        return match ($this->content_status) {
            self::STATUS_COMPLETE => '수동 등록',
            self::STATUS_PENDING => 'AI 대기',
            self::STATUS_GENERATED => 'AI 생성 (승인 대기)',
            self::STATUS_APPROVED => 'AI 승인됨',
            default => '알 수 없음',
        };
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_slang')
            ->withTimestamps();
    }

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class)
            ->withTimestamps();
    }

    public function examples(): HasMany
    {
        return $this->hasMany(SlangExample::class)
            ->orderBy('sort_order');
    }

    public function getAudioUrlAttribute(): ?string
    {
        return app(AudioFileService::class)->getUrl($this->audio_file, $this->audio_disk);
    }

    /**
     * 음성 파일이 스토리지에 물리적으로 존재하는지 확인.
     */
    public function hasAudioFile(): bool
    {
        return app(AudioFileService::class)->exists($this->audio_file, $this->audio_disk);
    }

    /**
     * 스토리지에서 음성 파일을 물리 삭제하고 DB 필드를 null로 초기화.
     */
    public function deleteAudioFile(): void
    {
        if ($this->audio_file) {
            app(AudioFileService::class)->delete($this->audio_file, $this->audio_disk);
            $this->update([
                'audio_file' => null,
                'audio_disk' => null,
            ]);
        }
    }

    public function hasThreadPostFormats(): bool
    {
        return is_array($this->thread_post_formats) && $this->thread_post_formats !== [];
    }

    public function clearThreadPostFormats(): void
    {
        $this->update([
            'thread_post_formats' => null,
            'thread_post_generated_at' => null,
        ]);
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            1 => 'Mild',
            2 => 'Moderate',
            3 => 'Strong',
            4 => 'Extreme',
            default => 'Unknown',
        };
    }

    public function getLevelKoreanLabelAttribute(): string
    {
        return match ($this->level) {
            1 => '순한맛',
            2 => '중간맛',
            3 => '매운맛',
            4 => '극한맛',
            default => '알 수 없음',
        };
    }

    public function getPublicTitleAttribute(): string
    {
        $publicTitle = trim((string) $this->public_title_en);

        if ($publicTitle !== '') {
            return $publicTitle;
        }

        return "{$this->korean} meaning in Korean";
    }

    public function getPublicSummaryAttribute(): string
    {
        $summary = trim((string) $this->public_summary_en);

        if ($summary !== '') {
            return $summary;
        }

        return trim((string) $this->english_description);
    }

    public function getResolvedSeoTitleAttribute(): string
    {
        $seoTitle = trim((string) $this->seo_title_en);

        if ($seoTitle !== '') {
            return $seoTitle;
        }

        return Str::limit($this->public_title, 60, '');
    }

    public function getResolvedSeoDescriptionAttribute(): string
    {
        $seoDescription = trim((string) $this->seo_description_en);

        if ($seoDescription !== '') {
            return $seoDescription;
        }

        return Str::limit($this->public_summary, 160);
    }
}
