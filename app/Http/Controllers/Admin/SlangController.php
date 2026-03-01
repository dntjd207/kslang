<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderSlangRequest;
use App\Http\Requests\Admin\StoreSlangRequest;
use App\Http\Requests\Admin\UpdateSlangRequest;
use App\Models\Category;
use App\Models\Slang;
use App\Services\AudioFileService;
use App\Services\SlangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlangController extends Controller
{
    public function __construct(
        private SlangService $slangService,
        private AudioFileService $audioFileService
    ) {}

    public function index(Request $request): View
    {
        $query = Slang::with('categories');

        if ($request->filled('search') && mb_strlen($request->search) >= 2) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('korean', 'like', "%{$search}%")
                    ->orWhere('pronunciation', 'like', "%{$search}%")
                    ->orWhere('english_description', 'like', "%{$search}%")
                    ->orWhere('korean_description', 'like', "%{$search}%")
                    ->orWhere('usage_context', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        $slangs = $query->orderBy('sort_order', 'asc')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('sort_order', 'asc')->get();

        return view('admin.slangs.index', [
            'pageTitle' => '욕/슬랭 관리',
            'slangs' => $slangs,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('sort_order', 'asc')->get();

        return view('admin.slangs.create', [
            'pageTitle' => '새 욕/슬랭 추가',
            'categories' => $categories,
        ]);
    }

    public function store(StoreSlangRequest $request): RedirectResponse
    {
        $this->slangService->create($request->validated());

        return redirect()
            ->route('admin.slangs.index')
            ->with('success', '욕/슬랭이 등록되었습니다.');
    }

    public function edit(Slang $slang): View
    {
        $slang->load(['categories', 'examples']);
        $categories = Category::orderBy('sort_order', 'asc')->get();

        return view('admin.slangs.edit', [
            'pageTitle' => '욕/슬랭 수정',
            'slang' => $slang,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateSlangRequest $request, Slang $slang): RedirectResponse
    {
        $this->slangService->update($slang, $request->validated());

        return redirect()
            ->route('admin.slangs.index')
            ->with('success', '욕/슬랭이 수정되었습니다.');
    }

    public function destroy(Slang $slang): JsonResponse
    {
        $this->slangService->delete($slang);

        return response()->json([
            'success' => true,
            'message' => '욕/슬랭이 삭제되었습니다.',
        ]);
    }

    public function reorder(ReorderSlangRequest $request): JsonResponse
    {
        $orders = $request->validated('orders');

        foreach ($orders as $item) {
            Slang::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => '정렬 순서가 저장되었습니다.',
        ]);
    }

    public function toggle(Slang $slang): JsonResponse
    {
        $slang->update([
            'is_active' => ! $slang->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => '활성 상태가 변경되었습니다.',
            'is_active' => $slang->is_active,
        ]);
    }

    /**
     * 음성 파일 단독 삭제 (AJAX).
     */
    public function destroyAudio(Slang $slang): JsonResponse
    {
        if (! $slang->audio_file) {
            return response()->json([
                'success' => false,
                'message' => '삭제할 음성 파일이 없습니다.',
            ], 404);
        }

        $this->audioFileService->delete($slang->audio_file);
        $slang->update(['audio_file' => null]);

        return response()->json([
            'success' => true,
            'message' => '음성 파일이 삭제되었습니다.',
        ]);
    }
}
