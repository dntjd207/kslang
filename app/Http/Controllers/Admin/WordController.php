<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Word;
use Illuminate\Support\Facades\Storage;

class WordController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $level = $request->input('level');

        $query = Word::query();

        if ($search) {
            $query->where('word_korean', 'like', "%{$search}%")
                  ->orWhere('word_english', 'like', "%{$search}%")
                  ->orWhere('meaning', 'like', "%{$search}%");
        }

        if ($level) {
            $query->where('level', $level);
        }

        $words = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.words.index', compact('words'));
    }

    public function create()
    {
        return view('admin.words.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'word_korean' => 'required|string|max:255',
            'word_english' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:5',
            'meaning' => 'nullable|string|max:255',
            'etymology' => 'nullable|string',
            'example_kr' => 'nullable|string|max:255',
            'example_en' => 'nullable|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120', // 5MB max
            'tags' => 'nullable|string',
        ]);

        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $filename = 'slang_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/audio', $filename);
            $data['audio_filename'] = $filename;
        }
        
        unset($data['audio']);

        Word::create($data);

        return redirect()->route('admin.words.index')->with('success', '단어가 성공적으로 생성되었습니다.');
    }

    public function edit(Word $word)
    {
        return view('admin.words.form', compact('word'));
    }

    public function update(Request $request, Word $word)
    {
        $data = $request->validate([
            'word_korean' => 'required|string|max:255',
            'word_english' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:5',
            'meaning' => 'nullable|string|max:255',
            'etymology' => 'nullable|string',
            'example_kr' => 'nullable|string|max:255',
            'example_en' => 'nullable|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'tags' => 'nullable|string',
        ]);

        if ($request->hasFile('audio')) {
            // Delete old file if exists
            if ($word->audio_filename) {
                Storage::delete('public/audio/' . $word->audio_filename);
            }

            $file = $request->file('audio');
            $filename = 'slang_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/audio', $filename);
            $data['audio_filename'] = $filename;
        }
        
        unset($data['audio']);

        $word->update($data);

        return redirect()->route('admin.words.index')->with('success', '단어가 성공적으로 수정되었습니다.');
    }

    public function destroy(Word $word)
    {
        if ($word->audio_filename) {
            Storage::delete('public/audio/' . $word->audio_filename);
        }

        $word->delete();

        return redirect()->route('admin.words.index')->with('success', '단어가 성공적으로 삭제되었습니다.');
    }
}

