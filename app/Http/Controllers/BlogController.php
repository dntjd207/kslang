<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $category = request()->filled('category') ? trim((string) request('category')) : null;
        $tag = request()->filled('tag') ? trim((string) request('tag')) : null;

        $blogPosts = BlogPost::query()
            ->published()
            ->with('slangs')
            ->when($category !== null, fn (Builder $query) => $query->where('category_name', $category))
            ->when($tag !== null, fn (Builder $query) => $query->where('tag_names', 'like', '%'.$tag.'%'))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.blog.index', [
            'blogPosts' => $blogPosts,
            'playStoreUrl' => AppSetting::getValue('play_store_url', ''),
            'activeCategory' => $category,
            'activeTag' => $tag,
            'availableCategories' => $this->getPublishedCategories(),
            'availableTags' => $this->getPublishedTags(),
        ]);
    }

    public function show(BlogPost $blogPost): View
    {
        abort_unless($blogPost->isPublished(), 404);

        $blogPost->load([
            'slangs' => fn ($query) => $query->publicVisible()->orderBy('korean'),
        ]);

        $relatedSlangIds = $blogPost->slangs->pluck('id')->all();

        $relatedPosts = BlogPost::query()
            ->published()
            ->whereKeyNot($blogPost->id)
            ->when(
                $relatedSlangIds !== [],
                fn (Builder $query) => $query->whereHas('slangs', function (Builder $slangQuery) use ($relatedSlangIds): void {
                    $slangQuery->whereIn('slangs.id', $relatedSlangIds);
                })
            )
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('public.blog.show', [
            'blogPost' => $blogPost,
            'relatedPosts' => $relatedPosts,
            'playStoreUrl' => AppSetting::getValue('play_store_url', ''),
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function getPublishedCategories()
    {
        return BlogPost::query()
            ->published()
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
    private function getPublishedTags()
    {
        return BlogPost::query()
            ->published()
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
}
