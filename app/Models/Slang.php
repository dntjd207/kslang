<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Slang extends Model
{
    protected $fillable = [
        'korean',
        'pronunciation',
        'english_description',
        'korean_description',
        'level',
        'usage_frequency',
        'usage_context',
        'audio_file',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
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
        if (! $this->audio_file) {
            return null;
        }

        return Storage::disk('public')->url($this->audio_file);
    }

    /**
     * 음성 파일이 스토리지에 물리적으로 존재하는지 확인.
     */
    public function hasAudioFile(): bool
    {
        if (! $this->audio_file) {
            return false;
        }

        return Storage::disk('public')->exists($this->audio_file);
    }

    /**
     * 스토리지에서 음성 파일을 물리 삭제하고 DB 필드를 null로 초기화.
     */
    public function deleteAudioFile(): void
    {
        if ($this->audio_file) {
            Storage::disk('public')->delete($this->audio_file);
            $this->update(['audio_file' => null]);
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
