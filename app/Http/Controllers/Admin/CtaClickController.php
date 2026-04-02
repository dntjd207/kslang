<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CtaClick;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CtaClickController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->input('range', '7d');
        $startDate = $this->resolveStartDate($range);

        $totalClicks = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->count();

        $clicksBySourceType = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->select('source_type', DB::raw('COUNT(*) as count'))
            ->groupBy('source_type')
            ->orderByDesc('count')
            ->pluck('count', 'source_type');

        $clicksByPlacement = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->select('placement', DB::raw('COUNT(*) as count'))
            ->groupBy('placement')
            ->orderByDesc('count')
            ->pluck('count', 'placement');

        $clicksByDate = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $topBlogPosts = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('blog_post_id')
            ->select('blog_post_id', DB::raw('COUNT(*) as count'))
            ->groupBy('blog_post_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('blogPost:id,title_ko,title_en,slug')
            ->get();

        $topSlangs = CtaClick::query()
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('slang_id')
            ->select('slang_id', DB::raw('COUNT(*) as count'))
            ->groupBy('slang_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('slang:id,korean,public_slug')
            ->get();

        $recentClicks = CtaClick::query()
            ->with(['blogPost:id,title_ko,slug', 'slang:id,korean,public_slug'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('admin.cta-clicks.index', [
            'pageTitle' => 'CTA 클릭 집계',
            'range' => $range,
            'startDate' => $startDate,
            'totalClicks' => $totalClicks,
            'clicksBySourceType' => $clicksBySourceType,
            'clicksByPlacement' => $clicksByPlacement,
            'clicksByDate' => $clicksByDate,
            'topBlogPosts' => $topBlogPosts,
            'topSlangs' => $topSlangs,
            'recentClicks' => $recentClicks,
        ]);
    }

    private function resolveStartDate(string $range): Carbon
    {
        return match ($range) {
            '1d' => now()->subDay()->startOfDay(),
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            'all' => Carbon::create(2020, 1, 1),
            default => now()->subDays(7)->startOfDay(),
        };
    }
}
