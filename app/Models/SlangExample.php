<?php

namespace App\Models;

use App\Services\AudioFileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlangExample extends Model
{
    protected $fillable = [
        'slang_id',
        'korean_example',
        'english_example',
        'audio_file',
        'audio_disk',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function slang(): BelongsTo
    {
        return $this->belongsTo(Slang::class);
    }

    public function getAudioUrlAttribute(): ?string
    {
        return app(AudioFileService::class)->getUrl($this->audio_file, $this->audio_disk);
    }

    public function hasAudioFile(): bool
    {
        return app(AudioFileService::class)->exists($this->audio_file, $this->audio_disk);
    }

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
}
