<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function show(string $slug): PageResource|JsonResponse
    {
        if ($slug === 'privacy') {
            return response()->json([
                'title' => 'Privacy Policy',
                'content' => view('public.privacy-content')->render(),
                'updated_at' => '2026-03-01T00:00:00+09:00',
            ]);
        }

        $page = Page::where('slug', $slug)->firstOrFail();

        return new PageResource($page);
    }
}
