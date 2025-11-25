<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];
}

