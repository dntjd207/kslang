<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuickStoreSlangRequest;
use App\Http\Requests\Admin\RegenerateSlangSectionRequest;
use App\Http\Requests\Admin\ReorderSlangRequest;
use App\Http\Requests\Admin\StoreSlangRequest;
use App\Http\Requests\Admin\UpdateSlangRequest;
use App\Models\Category;
use App\Models\Slang;
use App\Services\AudioFileService;
use App\Services\SlangAutoFillService;
use App\Services\SlangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlangController extends Controller
{
    public function __construct(
        private SlangService $slangService,
        private AudioFileService $audioFileService,
        private SlangAutoFillService $autoFillService
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
                    ->orWhere('usage_context', 'like', "%{$search}%")
                    ->orWhere('english_usage_context', 'like', "%{$search}%");
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

        if ($request->filled('content_status')) {
            $query->where('content_status', $request->content_status);
        }

        $slangs = $query->orderBy('sort_order', 'asc')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('sort_order', 'asc')->get();

        $statusCounts = Slang::query()
            ->selectRaw('content_status, COUNT(*) as count')
            ->groupBy('content_status')
            ->pluck('count', 'content_status');

        return view('admin.slangs.index', [
            'pageTitle' => '욕/슬랭 관리',
            'slangs' => $slangs,
            'categories' => $categories,
            'statusCounts' => $statusCounts,
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

    /**
     * 단어만 빠르게 등록 (AI 자동 생성 대기).
     */
    public function quickStore(QuickStoreSlangRequest $request): JsonResponse
    {
        $words = $request->parsedWords();

        if (empty($words)) {
            return response()->json([
                'success' => false,
                'message' => '유효한 단어가 없습니다.',
            ], 422);
        }

        $maxSortOrder = Slang::max('sort_order') ?? -1;
        $created = 0;
        $duplicates = [];

        foreach ($words as $word) {
            $exists = Slang::where('korean', $word)->exists();
            if ($exists) {
                $duplicates[] = $word;

                continue;
            }

            Slang::create([
                'korean' => $word,
                'pronunciation' => '',
                'english_description' => '',
                'korean_description' => '',
                'level' => 1,
                'usage_frequency' => 'Occasional',
                'usage_context' => '',
                'english_usage_context' => '',
                'sort_order' => ++$maxSortOrder,
                'is_active' => false,
                'content_status' => Slang::STATUS_PENDING,
            ]);

            $created++;
        }

        $message = "{$created}개 단어가 등록되었습니다.";
        if (! empty($duplicates)) {
            $message .= ' (중복 제외: '.implode(', ', $duplicates).')';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * AI 생성 콘텐츠 승인.
     */
    public function approve(Slang $slang): JsonResponse
    {
        if ($slang->content_status !== Slang::STATUS_GENERATED) {
            return response()->json([
                'success' => false,
                'message' => '승인할 수 없는 상태입니다.',
            ], 422);
        }

        $slang->update([
            'content_status' => Slang::STATUS_APPROVED,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "'{$slang->korean}' 콘텐츠가 승인되었습니다.",
        ]);
    }

    /**
     * AI 생성 콘텐츠 반려 (pending으로 되돌려 재생성 대기).
     */
    public function reject(Slang $slang): JsonResponse
    {
        if ($slang->content_status !== Slang::STATUS_GENERATED) {
            return response()->json([
                'success' => false,
                'message' => '반려할 수 없는 상태입니다.',
            ], 422);
        }

        $slang->examples()->delete();
        $slang->categories()->detach();

        $slang->update([
            'pronunciation' => '',
            'english_description' => '',
            'korean_description' => '',
            'level' => 1,
            'usage_frequency' => 'Occasional',
            'usage_context' => '',
            'english_usage_context' => '',
            'content_status' => Slang::STATUS_PENDING,
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "'{$slang->korean}' 콘텐츠가 반려되었습니다. 다음 자동 생성 시 재시도됩니다.",
        ]);
    }

    /**
     * 편집 폼의 특정 섹션만 AI로 다시 생성하여 JSON으로 반환.
     */
    public function regenerateSection(RegenerateSlangSectionRequest $request, Slang $slang): JsonResponse
    {
        $validated = $request->validated();

        try {
            $data = match ($validated['section']) {
                'descriptions' => $this->autoFillService->regenerateDescriptions($slang, $validated),
                'usage_context' => $this->autoFillService->regenerateUsageContext($slang, $validated),
                'examples' => $this->autoFillService->generateAdditionalExamples($slang, $validated, 3),
            };
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'AI 재생성에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'section' => $validated['section'],
            'data' => $data,
            'message' => match ($validated['section']) {
                'descriptions' => '설명이 다시 생성되었습니다.',
                'usage_context' => '사용 상황이 다시 생성되었습니다.',
                'examples' => '예문 3개가 추가 생성되었습니다.',
            },
        ]);
    }
}
