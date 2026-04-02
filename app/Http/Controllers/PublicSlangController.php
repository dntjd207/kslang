<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Slang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSlangController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));

        $slangs = Slang::query()
            ->publicVisible()
            ->with('categories')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('korean', 'like', "%{$search}%")
                        ->orWhere('pronunciation', 'like', "%{$search}%")
                        ->orWhere('english_description', 'like', "%{$search}%")
                        ->orWhere('public_title_en', 'like', "%{$search}%");
                });
            })
            ->orderForApiFeed()
            ->paginate(24)
            ->withQueryString();

        return view('public.slangs.index', [
            'slangs' => $slangs,
            'search' => $search,
            'playStoreUrl' => AppSetting::getValue('play_store_url', ''),
        ]);
    }

    public function show(Slang $slang): View
    {
        abort_unless(
            Slang::query()->publicVisible()->whereKey($slang->id)->exists(),
            404
        );

        $slang->load([
            'categories',
            'examples',
            'blogPosts' => fn ($query) => $query->published()->orderByDesc('published_at'),
        ]);

        $categoryIds = $slang->categories->pluck('id')->all();

        $relatedSlangs = Slang::query()
            ->publicVisible()
            ->whereKeyNot($slang->id)
            ->when(
                $categoryIds !== [],
                fn (Builder $query) => $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryIds): void {
                    $categoryQuery->whereIn('categories.id', $categoryIds);
                })
            )
            ->orderForApiFeed()
            ->limit(4)
            ->get();

        return view('public.slangs.show', [
            'slang' => $slang,
            'relatedSlangs' => $relatedSlangs,
            'playStoreUrl' => AppSetting::getValue('play_store_url', ''),
        ]);
    }
}
