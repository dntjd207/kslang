<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtaClick extends Model
{
    protected $fillable = [
        'target',
        'source_type',
        'placement',
        'blog_post_id',
        'slang_id',
        'page_url',
        'referer_url',
    ];

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    public function slang(): BelongsTo
    {
        return $this->belongsTo(Slang::class);
    }
}
