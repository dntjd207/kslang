<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
            'playStoreUrl' => AppSetting::getPlayStoreUrl(),
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

        [$bodyWithAnchors, $tocItems] = $this->parseHeadingsAndBuildToc((string) $blogPost->body_en);

        return view('public.blog.show', [
            'blogPost' => $blogPost,
            'relatedPosts' => $relatedPosts,
            'playStoreUrl' => AppSetting::getPlayStoreUrl(),
            'bodyHtml' => $bodyWithAnchors,
            'tocItems' => $tocItems,
        ]);
    }

    /**
     * HTML 본문에서 h2/h3 태그를 파싱하여 앵커 ID를 삽입하고 TOC 배열을 반환.
     *
     * @return array{0: string, 1: list<array{id: string, text: string, level: int}>}
     */
    private function parseHeadingsAndBuildToc(string $html): array
    {
        if (trim($html) === '') {
            return ['', []];
        }

        $tocItems = [];
        $slugCounts = [];

        $processed = preg_replace_callback(
            '/<(h[23])([^>]*)>(.*?)<\/\1>/is',
            function (array $matches) use (&$tocItems, &$slugCounts): string {
                $tag = $matches[1];
                $attrs = $matches[2];
                $innerHtml = $matches[3];
                $plainText = trim(strip_tags($innerHtml));

                $slug = Str::slug($plainText);

                if ($slug === '') {
                    $slug = 'section';
                }

                if (isset($slugCounts[$slug])) {
                    $slugCounts[$slug]++;
                    $slug = "{$slug}-{$slugCounts[$slug]}";
                } else {
                    $slugCounts[$slug] = 0;
                }

                $level = (int) substr($tag, 1);

                $tocItems[] = [
                    'id' => $slug,
                    'text' => $plainText,
                    'level' => $level,
                ];

                if (preg_match('/\bid=["\']/', $attrs)) {
                    return $matches[0];
                }

                return "<{$tag} id=\"{$slug}\"{$attrs}>{$innerHtml}</{$tag}>";
            },
            $html
        );

        return [$processed ?? $html, $tocItems];
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
