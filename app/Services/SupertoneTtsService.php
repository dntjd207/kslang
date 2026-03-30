<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SupertoneTtsService
{
    private const DEFAULT_STORAGE_DIRECTORY = 'audio/supertone-tts';

    private const RECENT_RESULTS_LIMIT = 10;

    public function hasConfiguredApiKey(): bool
    {
        return $this->getConfiguredApiKey() !== null;
    }

    public function hasConfiguredVoiceId(): bool
    {
        return $this->getConfiguredVoiceId() !== null;
    }

    public function getConfiguredVoiceId(): ?string
    {
        return $this->normalizeNullableString(config('services.supertone.voice_id'));
    }

    public function getMaskedConfiguredApiKey(): ?string
    {
        $apiKey = $this->getConfiguredApiKey();

        if ($apiKey === null) {
            return null;
        }

        if (strlen($apiKey) <= 8) {
            return str_repeat('*', strlen($apiKey));
        }

        return substr($apiKey, 0, 4)
            .str_repeat('*', strlen($apiKey) - 8)
            .substr($apiKey, -4);
    }

    public function getBaseUrl(): string
    {
        return rtrim((string) config('services.supertone.base_url', 'https://supertoneapi.com'), '/');
    }

    public function getStorageDisk(): string
    {
        return trim((string) config('services.supertone.storage_disk', 's3')) ?: 's3';
    }

    public function getStorageLocation(): string
    {
        $prefix = $this->getStoragePrefix();
        $bucket = $this->getStorageBucket();

        if ($this->getStorageDisk() === 's3') {
            if ($bucket !== null) {
                return 's3://'.$bucket.'/'.$prefix;
            }

            return 's3://'.$prefix;
        }

        return $this->getStorageDisk().'://'.$prefix;
    }

    public function usesTemporaryUrls(): bool
    {
        return (bool) config('services.supertone.use_temporary_url', true);
    }

    public function getTemporaryUrlMinutes(): int
    {
        return max(1, (int) config('services.supertone.temporary_url_minutes', 60));
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultInput(): array
    {
        return [
            'api_key' => '',
            'voice_id' => $this->getConfiguredVoiceId() ?? '',
            'text' => '',
            'language' => (string) config('services.supertone.default_language', 'ko'),
            'style' => $this->normalizeNullableString(config('services.supertone.default_style')) ?? '',
            'model' => (string) config('services.supertone.model', 'sona_speech_1'),
            'pitch_shift' => 0,
            'pitch_variance' => 1,
            'speed' => 1,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function generateAndStore(array $validated): array
    {
        $apiKey = $this->resolveApiKey($validated['api_key'] ?? null);
        $voiceId = $this->resolveVoiceId($validated['voice_id'] ?? null);
        $payload = $this->buildPayload($validated);
        $endpoint = $this->buildSpeechEndpoint($voiceId);
        $disk = $this->storageDisk();
        $startedAt = microtime(true);

        $response = Http::timeout(120)
            ->accept('audio/mpeg')
            ->withHeaders([
                'x-sup-api-key' => $apiKey,
            ])
            ->withQueryParameters([
                'output_format' => 'mp3',
            ])
            ->post($endpoint, $payload);

        $response->throw();

        $audioBinary = $response->body();

        if ($audioBinary === '') {
            throw new RuntimeException('Supertone API가 빈 오디오 데이터를 반환했습니다.');
        }

        $basePath = $this->generateBasePath();
        $audioPath = $basePath.'.mp3';
        $metadataPath = $basePath.'.json';
        $savedAt = now();

        if (! $this->writeToDisk($disk, $audioPath, $audioBinary)) {
            throw new RuntimeException('생성된 mp3 파일을 '.$this->getStorageDisk().' 디스크에 저장하지 못했습니다.');
        }

        $fileSizeBytes = (int) $disk->size($audioPath);
        $result = [
            'id' => pathinfo($audioPath, PATHINFO_FILENAME),
            'text' => (string) $validated['text'],
            'text_preview' => Str::limit((string) $validated['text'], 80),
            'voice_id' => $voiceId,
            'language' => (string) $validated['language'],
            'style' => $this->normalizeNullableString($validated['style'] ?? null),
            'model' => (string) $validated['model'],
            'voice_settings' => $this->buildVoiceSettings($validated),
            'audio_length_seconds' => $this->parseAudioLength($response->header('X-Audio-Length')),
            'request_duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'audio_path' => $audioPath,
            'audio_url' => $this->buildFileUrl($audioPath),
            'file_name' => basename($audioPath),
            'file_size_bytes' => $fileSizeBytes,
            'file_size_human' => $this->formatBytes($fileSizeBytes),
            'saved_at' => $savedAt->toIso8601String(),
            'saved_at_display' => $savedAt->format('Y-m-d H:i:s'),
            'endpoint' => $endpoint.'?output_format=mp3',
            'content_type' => (string) $response->header('Content-Type'),
            'storage_disk' => $this->getStorageDisk(),
            'storage_location' => $this->getStorageLocation(),
        ];

        $metadata = $result;
        unset($metadata['audio_url']);

        $metadataJson = json_encode(
            $metadata,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($metadataJson === false) {
            throw new RuntimeException('생성 결과 메타데이터를 인코딩하지 못했습니다.');
        }

        $metadataSaved = $this->writeToDisk($disk, $metadataPath, $metadataJson);

        if (! $metadataSaved) {
            throw new RuntimeException('생성 결과 메타데이터를 '.$this->getStorageDisk().' 디스크에 저장하지 못했습니다.');
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentResults(int $limit = self::RECENT_RESULTS_LIMIT): array
    {
        try {
            $disk = $this->storageDisk();
            $files = $disk->allFiles($this->getStoragePrefix());
        } catch (Throwable) {
            return [];
        }

        return collect($files)
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->sortByDesc(static fn (string $path): int => $disk->lastModified($path))
            ->take($limit)
            ->map(fn (string $path): ?array => $this->loadResultMetadata($path))
            ->filter()
            ->values()
            ->all();
    }

    private function getConfiguredApiKey(): ?string
    {
        return $this->normalizeNullableString(config('services.supertone.api_key'));
    }

    private function resolveApiKey(mixed $apiKey): string
    {
        $resolvedApiKey = $this->normalizeNullableString($apiKey) ?? $this->getConfiguredApiKey();

        if ($resolvedApiKey === null) {
            throw new RuntimeException('Supertone API Key가 설정되지 않았습니다. 환경값을 넣거나 폼에서 직접 입력해주세요.');
        }

        return $resolvedApiKey;
    }

    private function resolveVoiceId(mixed $voiceId): string
    {
        $resolvedVoiceId = $this->normalizeNullableString($voiceId) ?? $this->getConfiguredVoiceId();

        if ($resolvedVoiceId === null) {
            throw new RuntimeException('Supertone Voice ID가 설정되지 않았습니다. 환경값을 넣거나 폼에서 직접 입력해주세요.');
        }

        return $resolvedVoiceId;
    }

    private function buildSpeechEndpoint(string $voiceId): string
    {
        return $this->getBaseUrl().'/v1/text-to-speech/'.rawurlencode($voiceId);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildPayload(array $validated): array
    {
        $payload = [
            'text' => (string) $validated['text'],
            'language' => (string) $validated['language'],
            'model' => (string) $validated['model'],
        ];

        $style = $this->normalizeNullableString($validated['style'] ?? null);

        if ($style !== null) {
            $payload['style'] = $style;
        }

        $voiceSettings = $this->buildVoiceSettings($validated);

        if ($voiceSettings !== []) {
            $payload['voice_settings'] = $voiceSettings;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, float|int>
     */
    private function buildVoiceSettings(array $validated): array
    {
        $voiceSettings = [];

        if (array_key_exists('pitch_shift', $validated) && $validated['pitch_shift'] !== null) {
            $voiceSettings['pitch_shift'] = (int) $validated['pitch_shift'];
        }

        if (array_key_exists('pitch_variance', $validated) && $validated['pitch_variance'] !== null) {
            $voiceSettings['pitch_variance'] = (float) $validated['pitch_variance'];
        }

        if (array_key_exists('speed', $validated) && $validated['speed'] !== null) {
            $voiceSettings['speed'] = (float) $validated['speed'];
        }

        return $voiceSettings;
    }

    private function generateBasePath(): string
    {
        $datePath = now()->format('Y/m');
        $filename = 'tts-'.now()->format('Ymd-His').'-'.Str::lower((string) Str::uuid());

        return $this->getStoragePrefix().'/'.$datePath.'/'.$filename;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadResultMetadata(string $metadataPath): ?array
    {
        $disk = $this->storageDisk();

        if (! $disk->exists($metadataPath)) {
            return null;
        }

        try {
            $decoded = json_decode($disk->get($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($decoded)) {
            return null;
        }

        $audioPath = $decoded['audio_path'] ?? null;

        if (! is_string($audioPath) || ! $disk->exists($audioPath)) {
            return null;
        }

        $text = (string) ($decoded['text'] ?? '');
        $fileSizeBytes = (int) ($decoded['file_size_bytes'] ?? $disk->size($audioPath));

        return [
            'id' => (string) ($decoded['id'] ?? pathinfo($metadataPath, PATHINFO_FILENAME)),
            'text' => $text,
            'text_preview' => Str::limit($text, 80),
            'voice_id' => (string) ($decoded['voice_id'] ?? ''),
            'language' => (string) ($decoded['language'] ?? ''),
            'style' => $this->normalizeNullableString($decoded['style'] ?? null),
            'model' => (string) ($decoded['model'] ?? ''),
            'voice_settings' => is_array($decoded['voice_settings'] ?? null) ? $decoded['voice_settings'] : [],
            'audio_length_seconds' => $decoded['audio_length_seconds'] ?? null,
            'request_duration_ms' => (int) ($decoded['request_duration_ms'] ?? 0),
            'audio_path' => $audioPath,
            'audio_url' => $this->buildFileUrl($audioPath),
            'file_name' => (string) ($decoded['file_name'] ?? basename($audioPath)),
            'file_size_bytes' => $fileSizeBytes,
            'file_size_human' => $this->formatBytes($fileSizeBytes),
            'saved_at' => $this->resolveSavedAt($decoded['saved_at'] ?? null, $metadataPath),
            'saved_at_display' => $this->formatSavedAt($decoded['saved_at'] ?? null, $metadataPath),
            'endpoint' => (string) ($decoded['endpoint'] ?? ''),
            'content_type' => (string) ($decoded['content_type'] ?? 'audio/mpeg'),
            'storage_disk' => (string) ($decoded['storage_disk'] ?? $this->getStorageDisk()),
            'storage_location' => (string) ($decoded['storage_location'] ?? $this->getStorageLocation()),
        ];
    }

    private function parseAudioLength(mixed $audioLength): ?float
    {
        if (! is_string($audioLength) || trim($audioLength) === '') {
            return null;
        }

        return round((float) $audioLength, 2);
    }

    private function resolveSavedAt(mixed $savedAt, string $metadataPath): string
    {
        $savedAtValue = $this->normalizeNullableString($savedAt);

        if ($savedAtValue !== null) {
            return $savedAtValue;
        }

        return Carbon::createFromTimestamp(
            $this->storageDisk()->lastModified($metadataPath)
        )->toIso8601String();
    }

    private function formatSavedAt(mixed $savedAt, string $metadataPath): string
    {
        try {
            return Carbon::parse($this->resolveSavedAt($savedAt, $metadataPath))
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return Carbon::createFromTimestamp(
                $this->storageDisk()->lastModified($metadataPath)
            )->format('Y-m-d H:i:s');
        }
    }

    private function getStoragePrefix(): string
    {
        return trim((string) config('services.supertone.storage_prefix', self::DEFAULT_STORAGE_DIRECTORY), '/')
            ?: self::DEFAULT_STORAGE_DIRECTORY;
    }

    private function getStorageBucket(): ?string
    {
        return $this->normalizeNullableString(
            config('filesystems.disks.'.$this->getStorageDisk().'.bucket')
        );
    }

    private function storageDisk(): FilesystemAdapter
    {
        try {
            return Storage::disk($this->getStorageDisk());
        } catch (Throwable $e) {
            throw new RuntimeException(
                $this->getStorageDisk().' 디스크를 초기화하지 못했습니다. 필요한 스토리지 패키지와 설정을 확인해주세요.',
                previous: $e
            );
        }
    }

    private function shouldUseTemporaryUrl(FilesystemAdapter $disk): bool
    {
        return $this->usesTemporaryUrls() && $disk->providesTemporaryUrls();
    }

    private function buildFileUrl(string $path): string
    {
        $disk = $this->storageDisk();

        if ($this->shouldUseTemporaryUrl($disk)) {
            return $disk->temporaryUrl(
                $path,
                now()->addMinutes($this->getTemporaryUrlMinutes())
            );
        }

        return $disk->url($path);
    }

    private function writeToDisk(FilesystemAdapter $disk, string $path, string $contents): bool
    {
        try {
            return (bool) $disk->put($path, $contents);
        } catch (Throwable $e) {
            throw new RuntimeException(
                $this->getStorageDisk().' 디스크 저장 중 오류가 발생했습니다. AWS/S3 설정을 확인해주세요.',
                previous: $e
            );
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 2).' MB';
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
