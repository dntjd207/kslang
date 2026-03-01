<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SlangResource;
use App\Models\Slang;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SlangController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) ($request->input('per_page', 20)), 100);
        if ($perPage < 1) {
            $perPage = 20;
        }

        $query = Slang::with(['categories', 'examples'])
            ->where('is_active', true);

        if ($request->filled('level') && in_array((int) $request->level, [1, 2, 3, 4])) {
            $query->where('level', (int) $request->level);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', (int) $request->category_id);
            });
        }

        $slangs = $query->orderBy('sort_order', 'asc')
            ->paginate($perPage);

        return SlangResource::collection($slangs);
    }

    public function show(Slang $slang): SlangResource
    {
        if (! $slang->is_active) {
            abort(404, '요청한 리소스를 찾을 수 없습니다.');
        }

        $slang->load(['categories', 'examples']);

        return new SlangResource($slang);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $keyword = trim($request->input('q', ''));
        $perPage = min((int) ($request->input('per_page', 20)), 100);
        if ($perPage < 1) {
            $perPage = 20;
        }

        if (mb_strlen($keyword) < 2) {
            return SlangResource::collection(
                Slang::where('id', 0)->paginate($perPage)
            );
        }

        $slangs = Slang::with(['categories', 'examples'])
            ->where('is_active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('korean', 'like', "%{$keyword}%")
                    ->orWhere('pronunciation', 'like', "%{$keyword}%")
                    ->orWhere('english_description', 'like', "%{$keyword}%")
                    ->orWhere('korean_description', 'like', "%{$keyword}%");
            })
            ->orderBy('sort_order', 'asc')
            ->paginate($perPage);

        return SlangResource::collection($slangs);
    }
}
