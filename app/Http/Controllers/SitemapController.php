<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Slang;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pageTimestamps = Page::query()
            ->whereIn('slug', ['privacy', 'terms'])
            ->get()
            ->keyBy('slug');

        $latestBlogPost = BlogPost::query()
            ->published()
            ->orderByDesc('updated_at')
            ->first(['updated_at', 'published_at']);

        $latestPublicSlang = Slang::query()
            ->publicVisible()
            ->orderByDesc('updated_at')
            ->first(['updated_at']);

        $staticPages = [
            [
                'loc' => url('/'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => url('/privacy'),
                'lastmod' => $pageTimestamps->get('privacy')?->updated_at?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => url('/terms'),
                'lastmod' => $pageTimestamps->get('terms')?->updated_at?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => ($latestBlogPost?->updated_at ?? $latestBlogPost?->published_at ?? now())->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('slangs.public.index'),
                'lastmod' => ($latestPublicSlang?->updated_at ?? now())->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
        ];

        $blogPages = BlogPost::query()
            ->published()
            ->get(['slug', 'updated_at', 'published_at'])
            ->map(function (BlogPost $blogPost): array {
                return [
                    'loc' => route('blog.show', ['blogPost' => $blogPost->slug]),
                    'lastmod' => ($blogPost->updated_at ?? $blogPost->published_at ?? now())->toW3cString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            })
            ->all();

        $slangPages = Slang::query()
            ->publicVisible()
            ->get(['id', 'public_slug', 'updated_at'])
            ->map(function (Slang $slang): array {
                return [
                    'loc' => route('slangs.public.show', ['slang' => $slang->public_slug]),
                    'lastmod' => ($slang->updated_at ?? now())->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            })
            ->all();

        $pages = array_merge($staticPages, $blogPages, $slangPages);

        $xml = view('sitemap', compact('pages'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
