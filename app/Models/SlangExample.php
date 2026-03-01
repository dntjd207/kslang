<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlangExample extends Model
{
    protected $fillable = [
        'slang_id',
        'korean_example',
        'english_example',
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
}
