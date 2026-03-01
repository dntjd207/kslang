<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\SlangResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount(['slangs' => function ($query) {
            $query->where('is_active', true);
        }])
            ->orderBy('sort_order', 'asc')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        $perPage = min((int) ($request->input('per_page', 20)), 100);
        if ($perPage < 1) {
            $perPage = 20;
        }

        $slangs = $category->slangs()
            ->with(['categories', 'examples'])
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->paginate($perPage);

        $category->loadCount(['slangs' => function ($query) {
            $query->where('is_active', true);
        }]);

        return response()->json([
            'category' => new CategoryResource($category),
            'slangs' => SlangResource::collection($slangs)->response()->getData(true),
        ]);
    }
}
