<?php

namespace App\Services;

use App\Models\Slang;
use App\Models\SlangExample;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SlangService
{
    public function __construct(
        private AudioFileService $audioFileService
    ) {}

    public function create(array $data): Slang
    {
        return DB::transaction(function () use ($data) {
            $maxSortOrder = Slang::max('sort_order') ?? -1;

            $audioPath = null;
            if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
                $audioPath = $this->audioFileService->store($data['audio_file']);
            }

            $slang = Slang::create([
                'korean' => $data['korean'],
                'pronunciation' => $data['pronunciation'],
                'english_description' => $data['english_description'],
                'korean_description' => $data['korean_description'],
                'level' => $data['level'],
                'usage_frequency' => $data['usage_frequency'],
                'usage_context' => $data['usage_context'],
                'english_usage_context' => $data['english_usage_context'],
                'sort_order' => $maxSortOrder + 1,
                'is_active' => $data['is_active'] ?? true,
                'audio_file' => $audioPath,
            ]);

            if (! empty($data['category_ids'])) {
                $slang->categories()->sync($data['category_ids']);
            }

            $this->syncExamples($slang, $data['examples'] ?? []);

            return $slang;
        });
    }

    public function update(Slang $slang, array $data): Slang
    {
        return DB::transaction(function () use ($slang, $data) {
            $audioFile = $slang->audio_file;

            if (! empty($data['remove_audio']) && $audioFile) {
                $this->audioFileService->delete($audioFile);
                $audioFile = null;
            }

            if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
                $audioFile = $this->audioFileService->replace($data['audio_file'], $slang->audio_file);
            }

            $slang->update([
                'korean' => $data['korean'],
                'pronunciation' => $data['pronunciation'],
                'english_description' => $data['english_description'],
                'korean_description' => $data['korean_description'],
                'level' => $data['level'],
                'usage_frequency' => $data['usage_frequency'],
                'usage_context' => $data['usage_context'],
                'english_usage_context' => $data['english_usage_context'],
                'is_active' => $data['is_active'] ?? $slang->is_active,
                'audio_file' => $audioFile,
            ]);

            $slang->categories()->sync($data['category_ids'] ?? []);

            $this->syncExamples($slang, $data['examples'] ?? []);

            return $slang;
        });
    }

    public function delete(Slang $slang): void
    {
        DB::transaction(function () use ($slang) {
            if ($slang->audio_file) {
                $this->audioFileService->delete($slang->audio_file);
            }

            $slang->delete();
        });
    }

    private function syncExamples(Slang $slang, array $examples): void
    {
        $existingIds = $slang->examples()->pluck('id')->toArray();
        $incomingIds = [];

        foreach ($examples as $index => $example) {
            if (empty($example['korean_example']) && empty($example['english_example'])) {
                continue;
            }

            if (! empty($example['id'])) {
                SlangExample::where('id', $example['id'])
                    ->where('slang_id', $slang->id)
                    ->update([
                        'korean_example' => $example['korean_example'],
                        'english_example' => $example['english_example'],
                        'sort_order' => $index,
                    ]);
                $incomingIds[] = $example['id'];
            } else {
                $newExample = $slang->examples()->create([
                    'korean_example' => $example['korean_example'],
                    'english_example' => $example['english_example'],
                    'sort_order' => $index,
                ]);
                $incomingIds[] = $newExample->id;
            }
        }

        $toDelete = array_diff($existingIds, $incomingIds);
        if (! empty($toDelete)) {
            SlangExample::whereIn('id', $toDelete)
                ->where('slang_id', $slang->id)
                ->delete();
        }
    }
}
