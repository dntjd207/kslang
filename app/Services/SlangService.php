<?php

namespace App\Services;

use App\Models\Slang;
use App\Models\SlangExample;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SlangService
{
    public function __construct(
        private AudioFileService $audioFileService,
        private SupertoneTtsService $supertoneTtsService
    ) {}

    public function create(array $data): Slang
    {
        return DB::transaction(function () use ($data) {
            $maxSortOrder = Slang::max('sort_order') ?? -1;

            $audioPath = null;
            $audioDisk = null;

            if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
                $audioPath = $this->audioFileService->store($data['audio_file']);
                $audioDisk = $this->audioFileService->getDefaultDisk();
            }

            $slang = Slang::create([
                'korean' => $data['korean'],
                'ai_generation_hint' => $this->normalizeNullableString($data['ai_generation_hint'] ?? null),
                'pronunciation' => $data['pronunciation'],
                'english_description' => $data['english_description'],
                'korean_description' => $data['korean_description'],
                'level' => $data['level'],
                'usage_frequency' => $data['usage_frequency'],
                'usage_context' => $data['usage_context'],
                'english_usage_context' => $data['english_usage_context'],
                'sort_order' => $maxSortOrder + 1,
                'is_active' => $data['is_active'] ?? true,
                'is_new' => false,
                'approved_at' => null,
                'audio_file' => $audioPath,
                'audio_disk' => $audioDisk,
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
            $audioDisk = $slang->audio_disk;
            $shouldResetThreadPostFormats = $this->hasThreadContentSourceChanges($slang, $data);

            if (! empty($data['remove_audio']) && $audioFile) {
                $this->audioFileService->delete($audioFile, $audioDisk);
                $audioFile = null;
                $audioDisk = null;
            }

            if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
                $audioFile = $this->audioFileService->replace(
                    $data['audio_file'],
                    $slang->audio_file,
                    $slang->audio_disk
                );
                $audioDisk = $this->audioFileService->getDefaultDisk();
            }

            $updatePayload = [
                'korean' => $data['korean'],
                'ai_generation_hint' => $this->normalizeNullableString($data['ai_generation_hint'] ?? null),
                'pronunciation' => $data['pronunciation'],
                'english_description' => $data['english_description'],
                'korean_description' => $data['korean_description'],
                'level' => $data['level'],
                'usage_frequency' => $data['usage_frequency'],
                'usage_context' => $data['usage_context'],
                'english_usage_context' => $data['english_usage_context'],
                'is_active' => $data['is_active'] ?? $slang->is_active,
                'audio_file' => $audioFile,
                'audio_disk' => $audioDisk,
            ];

            if ($shouldResetThreadPostFormats) {
                $updatePayload['thread_post_formats'] = null;
                $updatePayload['thread_post_generated_at'] = null;
            }

            $slang->update($updatePayload);

            $slang->categories()->sync($data['category_ids'] ?? []);

            $this->syncExamples($slang, $data['examples'] ?? []);

            return $slang;
        });
    }

    public function delete(Slang $slang): void
    {
        DB::transaction(function () use ($slang) {
            foreach ($slang->examples as $example) {
                $this->deleteExampleAudio($example);
            }

            if ($slang->audio_file) {
                $this->audioFileService->delete($slang->audio_file, $slang->audio_disk);
            }

            $slang->delete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function generateSlangAudio(Slang $slang, string $text): array
    {
        return DB::transaction(function () use ($slang, $text) {
            $synthesized = $this->synthesizeKoreanMp3($text);

            $audioPath = $this->audioFileService->replaceGeneratedMp3(
                (string) $synthesized['binary'],
                $slang->audio_file,
                $slang->audio_disk,
                $this->audioFileService->getSlangsDirectory()
            );

            $audioDisk = $this->audioFileService->getDefaultDisk();

            $slang->update([
                'audio_file' => $audioPath,
                'audio_disk' => $audioDisk,
            ]);

            return [
                'text' => $text,
                'audio_file' => $audioPath,
                'audio_disk' => $audioDisk,
                'audio_url' => $this->audioFileService->getUrl($audioPath, $audioDisk),
                'audio_length_seconds' => $synthesized['audio_length_seconds'],
                'request_duration_ms' => $synthesized['request_duration_ms'],
                'endpoint' => $synthesized['endpoint'],
                'content_type' => $synthesized['content_type'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function generateExampleAudio(Slang $slang, ?SlangExample $example, string $text): array
    {
        return DB::transaction(function () use ($slang, $example, $text) {
            $synthesized = $this->synthesizeKoreanMp3($text);

            $audioPath = $this->audioFileService->replaceGeneratedMp3(
                (string) $synthesized['binary'],
                $example?->audio_file,
                $example?->audio_disk,
                $this->audioFileService->getSlangExamplesDirectory()
            );

            $audioDisk = $this->audioFileService->getDefaultDisk();

            if ($example !== null) {
                $example->update([
                    'audio_file' => $audioPath,
                    'audio_disk' => $audioDisk,
                ]);
            }

            return [
                'slang_id' => $slang->id,
                'example_id' => $example?->id,
                'persisted' => $example !== null,
                'text' => $text,
                'audio_file' => $audioPath,
                'audio_disk' => $audioDisk,
                'audio_url' => $this->audioFileService->getUrl($audioPath, $audioDisk),
                'audio_length_seconds' => $synthesized['audio_length_seconds'],
                'request_duration_ms' => $synthesized['request_duration_ms'],
                'endpoint' => $synthesized['endpoint'],
                'content_type' => $synthesized['content_type'],
            ];
        });
    }

    public function deleteExamplesForReset(Slang $slang): void
    {
        foreach ($slang->examples as $example) {
            $this->deleteExampleAudio($example);
        }

        $slang->examples()->delete();
    }

    public function approveGeneratedSlang(Slang $slang): Slang
    {
        $slang->update([
            'content_status' => Slang::STATUS_APPROVED,
            'is_active' => true,
            'is_new' => true,
            'approved_at' => now(),
        ]);

        return $slang->refresh();
    }

    public function rejectGeneratedSlang(Slang $slang): Slang
    {
        return DB::transaction(function () use ($slang) {
            $this->deleteExamplesForReset($slang);
            $slang->categories()->detach();

            $slang->update([
                'pronunciation' => '',
                'english_description' => '',
                'korean_description' => '',
                'level' => 1,
                'usage_frequency' => 'Occasional',
                'usage_context' => '',
                'english_usage_context' => '',
                'content_status' => Slang::STATUS_PENDING,
                'is_active' => false,
                'is_new' => false,
                'approved_at' => null,
                'thread_post_formats' => null,
                'thread_post_generated_at' => null,
            ]);

            return $slang->refresh();
        });
    }

    public function expireNewSlangs(int $days = 3): int
    {
        return Slang::query()
            ->where('is_new', true)
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', now()->subDays($days))
            ->update([
                'is_new' => false,
            ]);
    }

    private function syncExamples(Slang $slang, array $examples): void
    {
        $existingExamples = $slang->examples()->get()->keyBy('id');
        $existingIds = $existingExamples->keys()->all();
        $incomingIds = [];

        foreach ($examples as $index => $example) {
            if (empty($example['korean_example']) && empty($example['english_example'])) {
                continue;
            }

            $audioFile = $this->normalizeNullableString($example['audio_file'] ?? null);
            $audioDisk = $this->normalizeNullableString($example['audio_disk'] ?? null);

            if (! empty($example['id']) && $existingExamples->has((int) $example['id'])) {
                /** @var SlangExample $existingExample */
                $existingExample = $existingExamples->get((int) $example['id']);

                $this->deleteReplacedExampleAudio($existingExample, $audioFile, $audioDisk);

                $existingExample->update([
                    'korean_example' => $example['korean_example'],
                    'english_example' => $example['english_example'],
                    'audio_file' => $audioFile,
                    'audio_disk' => $audioDisk,
                    'sort_order' => $index,
                ]);

                $incomingIds[] = $existingExample->id;

                continue;
            }

            $newExample = $slang->examples()->create([
                'korean_example' => $example['korean_example'],
                'english_example' => $example['english_example'],
                'audio_file' => $audioFile,
                'audio_disk' => $audioDisk,
                'sort_order' => $index,
            ]);

            $incomingIds[] = $newExample->id;
        }

        $toDelete = array_diff($existingIds, $incomingIds);

        if (! empty($toDelete)) {
            $examplesToDelete = $existingExamples->only($toDelete);

            foreach ($examplesToDelete as $exampleToDelete) {
                $this->deleteExampleAudio($exampleToDelete);
            }

            SlangExample::whereIn('id', $toDelete)
                ->where('slang_id', $slang->id)
                ->delete();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function synthesizeKoreanMp3(string $text): array
    {
        return $this->supertoneTtsService->synthesize($text, [
            'language' => 'ko',
            'model' => config('services.supertone.model', 'sona_speech_1'),
            'speed' => 0.8,
        ]);
    }

    private function deleteExampleAudio(SlangExample $example): void
    {
        if ($example->audio_file) {
            $this->audioFileService->delete($example->audio_file, $example->audio_disk);
        }
    }

    private function deleteReplacedExampleAudio(SlangExample $example, ?string $newAudioFile, ?string $newAudioDisk): void
    {
        if (! $example->audio_file) {
            return;
        }

        if ($example->audio_file === $newAudioFile && $example->audio_disk === $newAudioDisk) {
            return;
        }

        $this->audioFileService->delete($example->audio_file, $example->audio_disk);
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasThreadContentSourceChanges(Slang $slang, array $data): bool
    {
        $currentFields = [
            'korean' => trim((string) $slang->korean),
            'ai_generation_hint' => $this->normalizeNullableString($slang->ai_generation_hint),
            'pronunciation' => trim((string) $slang->pronunciation),
            'english_description' => trim((string) $slang->english_description),
            'korean_description' => trim((string) $slang->korean_description),
            'level' => (int) $slang->level,
            'usage_frequency' => trim((string) $slang->usage_frequency),
            'usage_context' => trim((string) $slang->usage_context),
            'english_usage_context' => trim((string) $slang->english_usage_context),
        ];

        $incomingFields = [
            'korean' => trim((string) ($data['korean'] ?? '')),
            'ai_generation_hint' => $this->normalizeNullableString($data['ai_generation_hint'] ?? null),
            'pronunciation' => trim((string) ($data['pronunciation'] ?? '')),
            'english_description' => trim((string) ($data['english_description'] ?? '')),
            'korean_description' => trim((string) ($data['korean_description'] ?? '')),
            'level' => (int) ($data['level'] ?? 0),
            'usage_frequency' => trim((string) ($data['usage_frequency'] ?? '')),
            'usage_context' => trim((string) ($data['usage_context'] ?? '')),
            'english_usage_context' => trim((string) ($data['english_usage_context'] ?? '')),
        ];

        if ($currentFields !== $incomingFields) {
            return true;
        }

        $currentExamples = $this->normalizeExamplesForThreadComparison(
            $slang->examples()->get(['korean_example', 'english_example'])->toArray()
        );

        $incomingExamples = $this->normalizeExamplesForThreadComparison($data['examples'] ?? []);

        return $currentExamples !== $incomingExamples;
    }

    /**
     * @param  array<int, array<string, mixed>>  $examples
     * @return array<int, array{korean_example: string, english_example: string}>
     */
    private function normalizeExamplesForThreadComparison(array $examples): array
    {
        return collect($examples)
            ->filter(fn ($example) => is_array($example))
            ->map(function (array $example): array {
                return [
                    'korean_example' => trim((string) ($example['korean_example'] ?? '')),
                    'english_example' => trim((string) ($example['english_example'] ?? '')),
                ];
            })
            ->filter(function (array $example): bool {
                return $example['korean_example'] !== '' || $example['english_example'] !== '';
            })
            ->values()
            ->all();
    }
}
