<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AutoSaveBlogPostRequest;
use App\Http\Requests\Admin\GenerateBlogPostDraftRequest;
use App\Http\Requests\Admin\StoreBlogPostRequest;
use App\Http\Requests\Admin\TranslateBlogPostRequest;
use App\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Models\Slang;
use App\Services\BlogPostAiService;
use App\Services\BlogPostService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BlogPostController extends Controller
{
    public function __construct(
        private BlogPostService $blogPostService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search')),
            'status' => $request->filled('status') ? (string) $request->input('status') : null,
            'translation_status' => $request->filled('translation_status') ? (string) $request->input('translation_status') : null,
            'category' => $request->filled('category') ? trim((string) $request->input('category')) : null,
            'tag' => $request->filled('tag') ? trim((string) $request->input('tag')) : null,
        ];

        $blogPostsQuery = BlogPost::query()
            ->withCount('slangs');

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $blogPostsQuery->where(function (Builder $query) use ($search): void {
                $query->where('title_ko', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('primary_keyword', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($filters['status'] !== null) {
            $blogPostsQuery->where('status', $filters['status']);
        }

        if ($filters['translation_status'] !== null) {
            $blogPostsQuery->where('translation_status', $filters['translation_status']);
        }

        if ($filters['category'] !== null) {
            $blogPostsQuery->where('category_name', $filters['category']);
        }

        if ($filters['tag'] !== null) {
            $blogPostsQuery->where('tag_names', 'like', '%'.$filters['tag'].'%');
        }

        $blogPosts = $blogPostsQuery
            ->orderByRaw('case when published_at is null then 1 else 0 end asc')
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = BlogPost::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $translationCounts = BlogPost::query()
            ->selectRaw('translation_status, COUNT(*) as count')
            ->groupBy('translation_status')
            ->pluck('count', 'translation_status');

        return view('admin.blog-posts.index', [
            'pageTitle' => '블로그 글 관리',
            'blogPosts' => $blogPosts,
            'statusCounts' => $statusCounts,
            'translationCounts' => $translationCounts,
            'categoryOptions' => $this->getCategoryOptions(),
            'tagOptions' => $this->getTagOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.blog-posts.create', [
            'pageTitle' => '블로그 글 작성',
            'blogPost' => new BlogPost([
                'status' => BlogPost::STATUS_DRAFT,
                'translation_status' => BlogPost::TRANSLATION_NONE,
            ]),
            'relatedSlangs' => $this->getRelatedSlangs(),
            'searchIntentOptions' => BlogPost::SEARCH_INTENTS,
            'categoryOptions' => $this->getCategoryOptions(),
            'tagOptions' => $this->getTagOptions(),
        ]);
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $blogPost = $this->blogPostService->create($request->validated());

        return redirect()
            ->route('admin.blog-posts.edit', $blogPost)
            ->with('success', $blogPost->status === BlogPost::STATUS_PUBLISHED
                ? '블로그 글이 발행되었습니다.'
                : '블로그 글이 임시 저장되었습니다.');
    }

    public function show(BlogPost $blogPost): RedirectResponse
    {
        return redirect()->route('admin.blog-posts.edit', $blogPost);
    }

    public function edit(BlogPost $blogPost): View
    {
        $blogPost->load('slangs');

        return view('admin.blog-posts.edit', [
            'pageTitle' => '블로그 글 수정',
            'blogPost' => $blogPost,
            'relatedSlangs' => $this->getRelatedSlangs(),
            'searchIntentOptions' => BlogPost::SEARCH_INTENTS,
            'categoryOptions' => $this->getCategoryOptions(),
            'tagOptions' => $this->getTagOptions(),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $blogPost = $this->blogPostService->update($blogPost, $request->validated());

        return redirect()
            ->route('admin.blog-posts.edit', $blogPost)
            ->with('success', match ($blogPost->status) {
                BlogPost::STATUS_PUBLISHED => '블로그 글이 발행되었습니다.',
                BlogPost::STATUS_ARCHIVED => '블로그 글이 보관되었습니다.',
                default => '블로그 글이 임시 저장되었습니다.',
            });
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $this->blogPostService->delete($blogPost);

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', '블로그 글이 삭제되었습니다.');
    }

    public function generateDraft(GenerateBlogPostDraftRequest $request, BlogPostAiService $blogPostAiService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'AI 초안이 생성되었습니다.',
                'data' => $blogPostAiService->generateDraft($request->validated()),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'AI 초안 생성에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ], 500);
        }
    }

    public function translate(TranslateBlogPostRequest $request, BlogPostAiService $blogPostAiService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => '영어 번역본이 생성되었습니다.',
                'data' => $blogPostAiService->translate($request->validated()),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => '영어 번역 생성에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ], 500);
        }
    }

    public function autosave(AutoSaveBlogPostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $blogPostId = $validated['blog_post_id'] ?? null;
        $blogPost = $blogPostId !== null ? BlogPost::query()->find($blogPostId) : null;

        try {
            $blogPost = $this->blogPostService->autosave($validated, $blogPost);

            return response()->json([
                'success' => true,
                'message' => '임시 저장되었습니다.',
                'data' => [
                    'blog_post_id' => $blogPost->id,
                    'edit_url' => route('admin.blog-posts.edit', $blogPost),
                    'update_url' => route('admin.blog-posts.update', $blogPost),
                    'slug' => $blogPost->slug,
                    'category_name' => $blogPost->category_name,
                    'tag_names' => $blogPost->tag_names,
                    'status' => $blogPost->status,
                    'status_label' => $blogPost->status_label,
                    'translation_status' => $blogPost->translation_status,
                    'translation_status_label' => $blogPost->translation_status_label,
                    'translation_model' => $blogPost->translation_model,
                    'last_auto_saved_at' => $blogPost->last_auto_saved_at?->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? '자동 임시저장에 실패했습니다.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => '자동 임시저장에 실패했습니다.',
            ], 422);
        }
    }

    /**
     * @return Collection<int, Slang>
     */
    private function getRelatedSlangs()
    {
        return Slang::query()
            ->publicVisible()
            ->orderBy('korean')
            ->get(['id', 'korean', 'pronunciation', 'public_slug']);
    }

    /**
     * @return Collection<int, string>
     */
    private function getCategoryOptions(): Collection
    {
        return BlogPost::query()
            ->whereNotNull('category_name')
            ->orderBy('category_name')
            ->pluck('category_name')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function getTagOptions(): Collection
    {
        return BlogPost::query()
            ->whereNotNull('tag_names')
            ->pluck('tag_names')
            ->flatMap(function (?string $tagNames): array {
                return collect(explode(',', (string) $tagNames))
                    ->map(fn (string $tag) => trim($tag))
                    ->filter(fn (string $tag) => $tag !== '')
                    ->all();
            })
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->sort()
            ->values();
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'custom_name' => ['nullable', 'string', 'max:200'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $customName = trim((string) $request->input('custom_name'));

        if ($customName !== '') {
            $slug = Str::slug($customName);
        } else {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $slug = Str::slug($originalName);
        }

        if ($slug === '') {
            $slug = 'image-'.time();
        }

        $filename = $slug.'.'.$extension;
        $directory = 'blog-images';
        $disk = 'public';

        $counter = 1;
        while (Storage::disk($disk)->exists($directory.'/'.$filename)) {
            $filename = $slug.'-'.$counter.'.'.$extension;
            $counter++;
        }

        $path = $file->storeAs($directory, $filename, $disk);

        return response()->json([
            'location' => asset('storage/'.$path),
        ]);
    }

    public static function labelSearchIntent(string $value): string
    {
        return match ($value) {
            'meaning' => '뜻 설명형',
            'usage' => '사용법형',
            'comparison' => '비교형',
            'warning' => '주의형',
            'listicle' => '리스트형',
            'culture-context' => '문화/맥락형',
            default => Str::headline($value),
        };
    }
}
