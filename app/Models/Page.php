<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @var list<string> */
    public static array $allowedSlugs = ['privacy', 'terms'];

    protected $fillable = [
        'slug',
        'title',
        'content',
    ];

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function findBySlugOrFail(string $slug): self
    {
        return static::where('slug', $slug)->firstOrFail();
    }
}
