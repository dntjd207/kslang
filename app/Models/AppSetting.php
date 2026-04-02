<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    public const DEFAULT_PLAY_STORE_URL = 'https://play.google.com/store/apps/details?id=com.kslang.application';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function resolvePlayStoreUrl(?string $playStoreUrl): string
    {
        $resolvedPlayStoreUrl = trim((string) $playStoreUrl);

        return $resolvedPlayStoreUrl !== ''
            ? $resolvedPlayStoreUrl
            : self::DEFAULT_PLAY_STORE_URL;
    }

    public static function getPlayStoreUrl(): string
    {
        return static::resolvePlayStoreUrl(
            static::where('key', 'play_store_url')->value('value')
        );
    }

    public static function getValue(string $key, string $default = ''): string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, string>
     */
    public static function getMultiple(array $keys): array
    {
        $settings = static::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? '';
        }

        return $result;
    }
}
