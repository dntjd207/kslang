<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderCategoryRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('slangs')
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', [
            'pageTitle' => '카테고리 관리',
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $maxSortOrder = Category::max('sort_order') ?? -1;

        $category = Category::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '카테고리가 생성되었습니다.',
            'category' => $category->loadCount('slangs'),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => '카테고리가 수정되었습니다.',
            'category' => $category->loadCount('slangs'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => '카테고리가 삭제되었습니다.',
        ]);
    }

    public function reorder(ReorderCategoryRequest $request): JsonResponse
    {
        $orders = $request->validated('orders');

        foreach ($orders as $item) {
            Category::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => '정렬 순서가 저장되었습니다.',
        ]);
    }
}
