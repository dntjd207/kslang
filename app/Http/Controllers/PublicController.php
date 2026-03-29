<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function privacy(Request $request): View
    {
        $layout = $request->boolean('app')
            ? 'layouts.webview'
            : 'layouts.public';

        return view('public.privacy', compact('layout'));
    }

    public function terms(Request $request): View
    {
        $page = Page::findBySlugOrFail('terms');

        $layout = $request->boolean('app')
            ? 'layouts.webview'
            : 'layouts.public';

        return view('public.page', compact('page', 'layout'));
    }
}
