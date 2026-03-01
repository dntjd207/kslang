<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioFileService
{
    private const STORAGE_PATH = 'audio/slangs';

    private const DISK = 'public';

    /**
     * 음성 파일을 스토리지에 저장하고 상대 경로를 반환.
     */
    public function store(UploadedFile $file): string
    {
        $filename = Str::uuid().'.mp3';

        $file->storeAs(self::STORAGE_PATH, $filename, self::DISK);

        return self::STORAGE_PATH.'/'.$filename;
    }

    /**
     * 기존 파일을 삭제하고 새 파일을 저장. 새 파일의 상대 경로를 반환.
     */
    public function replace(UploadedFile $newFile, ?string $oldPath): string
    {
        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $this->store($newFile);
    }

    /**
     * 스토리지에서 음성 파일을 물리 삭제.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * 음성 파일의 공개 URL을 반환.
     */
    public function getUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }
}
