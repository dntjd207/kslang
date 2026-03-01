<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = [
            [
                'loc' => url('/'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => url('/privacy'),
                'lastmod' => Page::where('slug', 'privacy')->value('updated_at')?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => url('/terms'),
                'lastmod' => Page::where('slug', 'terms')->value('updated_at')?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
        ];

        $xml = view('sitemap', compact('pages'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
