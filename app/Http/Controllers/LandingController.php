<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Slang;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $previewSlangs = Slang::query()
            ->publicVisible()
            ->whereIn('level', [1, 2])
            ->orderBy('sort_order', 'asc')
            ->limit(8)
            ->get();

        $playStoreUrl = AppSetting::getPlayStoreUrl();

        return view('public.landing', [
            'previewSlangs' => $previewSlangs,
            'playStoreUrl' => $playStoreUrl,
        ]);
    }
}
