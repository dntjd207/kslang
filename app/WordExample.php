<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WordExample extends Model
{
    protected $fillable = [
        'word_id',
        'example_kr',
        'example_en',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}

