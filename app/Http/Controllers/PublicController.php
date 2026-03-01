<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function privacy(Request $request): View
    {
        return $this->showPage('privacy', $request);
    }

    public function terms(Request $request): View
    {
        return $this->showPage('terms', $request);
    }

    private function showPage(string $slug, Request $request): View
    {
        $page = Page::findBySlugOrFail($slug);

        $layout = $request->boolean('app')
            ? 'layouts.webview'
            : 'layouts.public';

        return view('public.page', compact('page', 'layout'));
    }
}
