<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $fillable = [
        'word_korean',
        'word_english',
        'level',
        'meaning',
        'etymology',
        'example_kr',
        'example_en',
        'audio_filename',
        'tags',
    ];

    protected $casts = [
        'level' => 'integer',
    ];
}

