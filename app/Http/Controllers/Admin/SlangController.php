<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DetailedStoreSlangRequest;
use App\Http\Requests\Admin\GenerateSlangAudioRequest;
use App\Http\Requests\Admin\GenerateSlangExampleAudioRequest;
use App\Http\Requests\Admin\QuickStoreSlangRequest;
use App\Http\Requests\Admin\RegenerateSlangSectionRequest;
use App\Http\Requests\Admin\ReorderSlangRequest;
use App\Http\Requests\Admin\StoreSlangRequest;
use App\Http\Requests\Admin\UpdateSlangRequest;
use App\Models\Category;
use App\Models\Slang;
use App\Models\SlangExample;
use App\Services\AudioFileService;
use App\Services\SlangAutoFillService;
use App\Services\SlangService;
use App\Services\SlangThreadContentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SlangController extends Controller
{
    public function __construct(
        private SlangService $slangService,
        private AudioFileService $audioFileService
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->extractIndexFilters($request);
        $hasAppliedFilters = $this->hasAppliedIndexFilters($filters);

        $slangQuery = Slang::query()->with('categories');
        $this->applyIndexFilters($slangQuery, $filters);

        $slangs = $slangQuery
            ->orderBy('sort_order', 'asc')
            ->get();

        $categories = Category::query()
            ->orderBy('sort_order', 'asc')
            ->withCount([
                'slangs as filtered_slangs_count' => function (Builder $query) use ($filters): void {
                    $this->applyIndexFilters($query, $filters, ['category_id']);
                },
            ])
            ->get();

        $statusCountsQuery = Slang::query();
        $this->applyIndexFilters($statusCountsQuery, $filters, ['content_status']);
        $statusCounts = $statusCountsQuery
            ->selectRaw('content_status, COUNT(*) as count')
            ->groupBy('content_status')
            ->pluck('count', 'content_status');

        $categoryTotalQuery = Slang::query();
        $this->applyIndexFilters($categoryTotalQuery, $filters, ['category_id']);
        $categoryTotalCount = $categoryTotalQuery->count();

        return view('admin.slangs.index', [
            'pageTitle' => '욕/슬랭 관리',
            'slangs' => $slangs,
            'categories' => $categories,
            'statusCounts' => $statusCounts,
            'categoryTotalCount' => $categoryTotalCount,
            'hasAppliedFilters' => $hasAppliedFilters,
            'isReorderable' => ! $hasAppliedFilters,
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

        $this->audioFileService->delete($slang->audio_file, $slang->audio_disk);
        $slang->update([
            'audio_file' => null,
            'audio_disk' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '음성 파일이 삭제되었습니다.',
        ]);
    }

    public function generateAudio(GenerateSlangAudioRequest $request, Slang $slang): JsonResponse
    {
        try {
            $result = $this->slangService->generateSlangAudio($slang, (string) $request->validated('text'));

            return response()->json([
                'success' => true,
                'message' => '슬랭 mp3 생성이 완료되었습니다.',
                'result' => $result,
            ]);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Supertone API 서버에 연결하지 못했습니다.',
            ], 502);
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extractRequestExceptionMessage($e),
            ], $e->response?->status() ?? 502);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => '슬랭 mp3 생성 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    public function generateExampleAudio(GenerateSlangExampleAudioRequest $request, Slang $slang): JsonResponse
    {
        $example = null;
        $exampleId = $request->validated('example_id');

        if ($exampleId !== null) {
            $example = SlangExample::query()
                ->where('slang_id', $slang->id)
                ->find($exampleId);
        }

        try {
            $result = $this->slangService->generateExampleAudio(
                $slang,
                $example,
                (string) $request->validated('text')
            );

            return response()->json([
                'success' => true,
                'message' => $example !== null
                    ? '예문 mp3 생성과 저장이 완료되었습니다.'
                    : '예문 mp3가 생성되었습니다. 전체 저장 시 DB에 반영됩니다.',
                'result' => [
                    ...$result,
                    'example_index' => (int) $request->validated('example_index'),
                ],
            ]);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Supertone API 서버에 연결하지 못했습니다.',
            ], 502);
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extractRequestExceptionMessage($e),
            ], $e->response?->status() ?? 502);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => '예문 mp3 생성 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    public function showThreadPosts(Slang $slang): JsonResponse
    {
        if (! $slang->hasThreadPostFormats()) {
            return response()->json([
                'success' => false,
                'message' => '저장된 Thread 콘텐츠가 없습니다. 먼저 생성해주세요.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => '저장된 Thread 콘텐츠를 불러왔습니다.',
            'data' => $this->buildThreadPostsPayload($slang),
        ]);
    }

    public function generateThreadPosts(Slang $slang, SlangThreadContentService $threadContentService): JsonResponse
    {
        try {
            $slang = $threadContentService->generateAndStore($slang);

            return response()->json([
                'success' => true,
                'message' => 'Thread 콘텐츠 4종을 생성하고 저장했습니다.',
                'data' => $this->buildThreadPostsPayload($slang),
            ]);
        } catch (Throwable $e) {
            Log::error('Thread content generation failed.', [
                'slang_id' => $slang->id,
                'error' => $e->getMessage(),
            ]);
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Thread 콘텐츠 생성에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ], 500);
        }
    }

    /**
     * 단어 + 설명을 함께 등록하여 AI 생성 힌트를 저장.
     */
    public function detailedStore(DetailedStoreSlangRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $word = trim((string) $validated['korean']);

        if (Slang::where('korean', $word)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "'{$word}' 단어는 이미 등록되어 있습니다.",
            ], 422);
        }

        $maxSortOrder = Slang::max('sort_order') ?? -1;

        $this->createPendingSlang($word, ++$maxSortOrder, $validated['ai_generation_hint']);

        return response()->json([
            'success' => true,
            'message' => "'{$word}' 단어가 설명과 함께 등록되었습니다.",
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

            $this->createPendingSlang($word, ++$maxSortOrder);

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

        $this->slangService->approveGeneratedSlang($slang);

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

        $this->slangService->rejectGeneratedSlang($slang);

        return response()->json([
            'success' => true,
            'message' => "'{$slang->korean}' 콘텐츠가 반려되었습니다. 다음 자동 생성 시 재시도됩니다.",
        ]);
    }

    /**
     * 편집 폼의 특정 섹션만 AI로 다시 생성하여 JSON으로 반환.
     */
    public function regenerateSection(
        RegenerateSlangSectionRequest $request,
        Slang $slang,
        SlangAutoFillService $autoFillService
    ): JsonResponse {
        $validated = $request->validated();

        try {
            $data = match ($validated['section']) {
                'descriptions' => $autoFillService->regenerateDescriptions($slang, $validated),
                'usage_context' => $autoFillService->regenerateUsageContext($slang, $validated),
                'examples' => $autoFillService->generateAdditionalExamples($slang, $validated, 3),
                'seo_fields' => $autoFillService->generateSeoFields($slang, $validated),
                'faq' => $this->generateAndStoreFaq($slang, $autoFillService, $validated),
            };
        } catch (Throwable $e) {
            Log::error('Slang section regeneration failed.', [
                'slang_id' => $slang->id,
                'section' => $validated['section'] ?? null,
                'error' => $e->getMessage(),
            ]);
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
                'seo_fields' => '공개 SEO 필드가 생성되었습니다.',
                'faq' => 'FAQ가 생성되었습니다.',
            },
        ]);
    }

    /**
     * @return array{search:string, level:?int, category_id:?int, content_status:?string}
     */
    private function extractIndexFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search')),
            'level' => $request->filled('level') ? $request->integer('level') : null,
            'category_id' => $request->filled('category_id') ? $request->integer('category_id') : null,
            'content_status' => $request->filled('content_status') ? (string) $request->input('content_status') : null,
        ];
    }

    /**
     * @param  array{search:string, level:?int, category_id:?int, content_status:?string}  $filters
     * @param  array<int, string>  $ignoredFilters
     */
    private function applyIndexFilters(Builder $query, array $filters, array $ignoredFilters = []): void
    {
        if (
            ! in_array('search', $ignoredFilters, true)
            && $filters['search'] !== ''
            && mb_strlen($filters['search']) >= 2
        ) {
            $search = $filters['search'];

            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where('korean', 'like', "%{$search}%")
                    ->orWhere('ai_generation_hint', 'like', "%{$search}%")
                    ->orWhere('pronunciation', 'like', "%{$search}%")
                    ->orWhere('english_description', 'like', "%{$search}%")
                    ->orWhere('korean_description', 'like', "%{$search}%")
                    ->orWhere('usage_context', 'like', "%{$search}%")
                    ->orWhere('english_usage_context', 'like', "%{$search}%");
            });
        }

        if (! in_array('level', $ignoredFilters, true) && $filters['level'] !== null) {
            $query->where('level', $filters['level']);
        }

        if (! in_array('category_id', $ignoredFilters, true) && $filters['category_id'] !== null) {
            $categoryId = $filters['category_id'];

            $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryId): void {
                $categoryQuery->where('categories.id', $categoryId);
            });
        }

        if (! in_array('content_status', $ignoredFilters, true) && $filters['content_status'] !== null) {
            $query->where('content_status', $filters['content_status']);
        }
    }

    /**
     * @param  array{search:string, level:?int, category_id:?int, content_status:?string}  $filters
     */
    private function hasAppliedIndexFilters(array $filters): bool
    {
        return ($filters['search'] !== '' && mb_strlen($filters['search']) >= 2)
            || $filters['level'] !== null
            || $filters['category_id'] !== null
            || $filters['content_status'] !== null;
    }

    private function createPendingSlang(string $word, int $sortOrder, ?string $aiGenerationHint = null): Slang
    {
        $normalizedHint = trim((string) $aiGenerationHint);

        return Slang::create([
            'korean' => $word,
            'ai_generation_hint' => $normalizedHint !== '' ? $normalizedHint : null,
            'pronunciation' => '',
            'english_description' => '',
            'korean_description' => '',
            'level' => 1,
            'usage_frequency' => 'Occasional',
            'usage_context' => '',
            'english_usage_context' => '',
            'sort_order' => $sortOrder,
            'is_active' => false,
            'content_status' => Slang::STATUS_PENDING,
            'is_new' => false,
            'approved_at' => null,
        ]);
    }

    /**
     * @return array{
     *     slang_id: int,
     *     korean: string,
     *     has_saved_formats: bool,
     *     generated_at: ?string,
     *     formats: array<string, mixed>
     * }
     */
    private function buildThreadPostsPayload(Slang $slang): array
    {
        return [
            'slang_id' => $slang->id,
            'korean' => $slang->korean,
            'has_saved_formats' => $slang->hasThreadPostFormats(),
            'generated_at' => $slang->thread_post_generated_at?->format('Y-m-d H:i'),
            'formats' => $slang->thread_post_formats ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{faq_items: list<array{question: string, answer: string}>}
     */
    private function generateAndStoreFaq(Slang $slang, SlangAutoFillService $autoFillService, array $context): array
    {
        $result = $autoFillService->generateFaqItems($slang, $context, 5);

        if (! empty($result['faq_items'])) {
            $slang->update(['faq_items' => $result['faq_items']]);
        }

        return $result;
    }

    private function extractRequestExceptionMessage(RequestException $exception): string
    {
        $response = $exception->response;

        if ($response === null) {
            return 'Supertone API 요청에 실패했습니다.';
        }

        $decoded = json_decode($response->body(), true);

        if (is_array($decoded)) {
            $message = $decoded['message']
                ?? $decoded['error']
                ?? $decoded['detail']
                ?? data_get($decoded, 'error.message');

            if (is_string($message) && trim($message) !== '') {
                return trim($message);
            }
        }

        $body = trim($response->body());

        if ($body !== '') {
            return Str::limit($body, 300);
        }

        return 'Supertone API 요청에 실패했습니다.';
    }
}
