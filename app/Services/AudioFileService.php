<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AudioFileService
{
    public function store(UploadedFile $file, ?string $directory = null): string
    {
        return $this->storeUploadedFile($file, $directory ?? $this->getSlangsDirectory());
    }

    public function replace(
        UploadedFile $newFile,
        ?string $oldPath,
        ?string $oldDisk = null,
        ?string $directory = null
    ): string {
        if ($oldPath) {
            $this->delete($oldPath, $oldDisk);
        }

        return $this->store($newFile, $directory);
    }

    public function storeGeneratedMp3(string $contents, ?string $directory = null): string
    {
        return $this->storeContents($contents, $directory ?? $this->getSlangsDirectory(), 'mp3');
    }

    public function replaceGeneratedMp3(
        string $contents,
        ?string $oldPath,
        ?string $oldDisk = null,
        ?string $directory = null
    ): string {
        if ($oldPath) {
            $this->delete($oldPath, $oldDisk);
        }

        return $this->storeGeneratedMp3($contents, $directory);
    }

    public function delete(?string $path, ?string $disk = null): void
    {
        if (! $path) {
            return;
        }

        $resolvedDisk = $this->resolveDiskName($path, $disk);
        $filesystem = Storage::disk($resolvedDisk);

        if ($filesystem->exists($path)) {
            $filesystem->delete($path);
        }
    }

    public function getUrl(?string $path, ?string $disk = null): ?string
    {
        if (! $path) {
            return null;
        }

        $resolvedDisk = $this->resolveDiskName($path, $disk);
        $filesystem = Storage::disk($resolvedDisk);

        if (
            $resolvedDisk === $this->getDefaultDisk()
            && $this->usesTemporaryUrls()
            && $filesystem->providesTemporaryUrls()
        ) {
            return $filesystem->temporaryUrl(
                $path,
                now()->addMinutes($this->getTemporaryUrlMinutes())
            );
        }

        return $filesystem->url($path);
    }

    public function exists(?string $path, ?string $disk = null): bool
    {
        if (! $path) {
            return false;
        }

        $resolvedDisk = $this->resolveDiskName($path, $disk);

        return Storage::disk($resolvedDisk)->exists($path);
    }

    public function getDefaultDisk(): string
    {
        return trim((string) config('services.audio.disk', 's3')) ?: 's3';
    }

    public function getLegacyDisk(): string
    {
        return trim((string) config('services.audio.legacy_disk', 'public')) ?: 'public';
    }

    public function getSlangsDirectory(): string
    {
        return trim((string) config('services.audio.slangs_directory', 'audio/slangs'), '/')
            ?: 'audio/slangs';
    }

    public function getSlangExamplesDirectory(): string
    {
        return trim((string) config('services.audio.slang_examples_directory', 'audio/slang-examples'), '/')
            ?: 'audio/slang-examples';
    }

    public function usesTemporaryUrls(): bool
    {
        return (bool) config('services.audio.use_temporary_url', true);
    }

    public function getTemporaryUrlMinutes(): int
    {
        return max(1, (int) config('services.audio.temporary_url_minutes', 60));
    }

    private function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        $relativePath = $this->buildRelativePath($directory, 'mp3');

        $this->storageDisk()->putFileAs(
            dirname($relativePath),
            $file,
            basename($relativePath)
        );

        return $relativePath;
    }

    private function storeContents(string $contents, string $directory, string $extension): string
    {
        $relativePath = $this->buildRelativePath($directory, $extension);

        if (! $this->storageDisk()->put($relativePath, $contents)) {
            throw new RuntimeException($this->getDefaultDisk().' 디스크에 음성 파일을 저장하지 못했습니다.');
        }

        return $relativePath;
    }

    private function buildRelativePath(string $directory, string $extension): string
    {
        $normalizedDirectory = trim($directory, '/');

        return $normalizedDirectory.'/'.Str::uuid().'.'.$extension;
    }

    private function storageDisk(): FilesystemAdapter
    {
        try {
            return Storage::disk($this->getDefaultDisk());
        } catch (Throwable $e) {
            throw new RuntimeException(
                $this->getDefaultDisk().' 디스크를 초기화하지 못했습니다. 스토리지 설정을 확인해주세요.',
                previous: $e
            );
        }
    }

    private function resolveDiskName(string $path, ?string $disk = null): string
    {
        if ($disk !== null && trim($disk) !== '') {
            return trim($disk);
        }

        $defaultDisk = $this->getDefaultDisk();

        try {
            if (Storage::disk($defaultDisk)->exists($path)) {
                return $defaultDisk;
            }
        } catch (Throwable) {
            // default disk 접근 실패 시 레거시 디스크 fallback 시도
        }

        $legacyDisk = $this->getLegacyDisk();

        try {
            if ($legacyDisk !== $defaultDisk && Storage::disk($legacyDisk)->exists($path)) {
                return $legacyDisk;
            }
        } catch (Throwable) {
            // 레거시 디스크도 접근 실패하면 기본 디스크명을 그대로 반환
        }

        return $defaultDisk;
    }
}
