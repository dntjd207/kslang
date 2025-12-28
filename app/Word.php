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
        'audio_filename',
        'tags',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function examples()
    {
        return $this->hasMany(WordExample::class)->orderBy('sort_order');
    }
}
