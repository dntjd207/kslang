<?php

namespace App\Models;

use App\Services\AudioFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * API에 노출 가능한 슬랭만 조회하는 스코프.
     * content_status가 'complete' 또는 'approved'이고, is_active가 true인 것만 반환.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeApiVisible($query)
    {
        return $query->where('is_active', true)
            ->whereIn('content_status', [self::STATUS_COMPLETE, self::STATUS_APPROVED]);
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
}
