<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

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
