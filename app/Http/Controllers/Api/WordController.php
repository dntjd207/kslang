<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Word;

class WordController extends Controller
{
    public function index()
    {
        $words = Word::with(['examples' => function($query) {
            $query->orderBy('sort_order');
        }])->get();

        return response()->json([
            'count' => $words->count(),
            'data' => $words
        ]);
    }
}
