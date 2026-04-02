<?php

namespace App\Http\Controllers;

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
        $layout = $request->boolean('app')
            ? 'layouts.webview'
            : 'layouts.public';

        return view('public.terms', compact('layout'));
    }
}
