<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Word;
use App\WordExample;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'tags' => 'nullable|string',
            'examples' => 'nullable|array',
            'examples.*.example_kr' => 'required|string|max:255',
            'examples.*.example_en' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $filename = 'slang_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/audio', $filename);
            $data['audio_filename'] = $filename;
        }
        
        unset($data['audio']);
        unset($data['examples']);

        DB::beginTransaction();
        try {
            $word = Word::create($data);

            // 예시 저장
            if ($request->has('examples')) {
                foreach ($request->input('examples') as $index => $example) {
                    if (!empty($example['example_kr'])) {
                        WordExample::create([
                            'word_id' => $word->id,
                            'example_kr' => $example['example_kr'],
                            'example_en' => $example['example_en'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('admin.words.index')->with('success', '단어가 성공적으로 생성되었습니다.');
    }

    public function edit(Word $word)
    {
        $word->load('examples');
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
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'tags' => 'nullable|string',
            'examples' => 'nullable|array',
            'examples.*.id' => 'nullable|integer',
            'examples.*.example_kr' => 'required|string|max:255',
            'examples.*.example_en' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('audio')) {
            if ($word->audio_filename) {
                Storage::delete('public/audio/' . $word->audio_filename);
            }

            $file = $request->file('audio');
            $filename = 'slang_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/audio', $filename);
            $data['audio_filename'] = $filename;
        }
        
        unset($data['audio']);
        unset($data['examples']);

        DB::beginTransaction();
        try {
            $word->update($data);

            // 기존 예시 ID 목록
            $existingIds = $word->examples->pluck('id')->toArray();
            $updatedIds = [];

            // 예시 업데이트/생성
            if ($request->has('examples')) {
                foreach ($request->input('examples') as $index => $example) {
                    if (!empty($example['example_kr'])) {
                        if (!empty($example['id'])) {
                            // 기존 예시 업데이트
                            $wordExample = WordExample::find($example['id']);
                            if ($wordExample && $wordExample->word_id == $word->id) {
                                $wordExample->update([
                                    'example_kr' => $example['example_kr'],
                                    'example_en' => $example['example_en'] ?? null,
                                    'sort_order' => $index,
                                ]);
                                $updatedIds[] = $wordExample->id;
                            }
                        } else {
                            // 새 예시 생성
                            $newExample = WordExample::create([
                                'word_id' => $word->id,
                                'example_kr' => $example['example_kr'],
                                'example_en' => $example['example_en'] ?? null,
                                'sort_order' => $index,
                            ]);
                            $updatedIds[] = $newExample->id;
                        }
                    }
                }
            }

            // 삭제된 예시 제거
            $toDelete = array_diff($existingIds, $updatedIds);
            WordExample::whereIn('id', $toDelete)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

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

    public function reorderExamples(Request $request, Word $word)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->input('order') as $index => $id) {
            WordExample::where('id', $id)
                ->where('word_id', $word->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
