<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Word;

class WordController extends Controller
{
    public function index()
    {
        $words = Word::all();

        return response()->json([
            'count' => $words->count(),
            'data' => $words
        ]);
    }
}

