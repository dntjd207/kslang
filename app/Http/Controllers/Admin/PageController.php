<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function edit(string $slug): View
    {
        $page = Page::findBySlugOrFail($slug);

        $pageTitle = match ($slug) {
            'privacy' => '개인정보처리방침 수정',
            'terms' => '이용약관 수정',
            default => '페이지 수정',
        };

        return view('admin.pages.edit', compact('page', 'pageTitle'));
    }

    public function update(UpdatePageRequest $request, string $slug): RedirectResponse
    {
        $page = Page::findBySlugOrFail($slug);

        $page->update([
            'content' => clean($request->validated('content')),
        ]);

        $label = match ($slug) {
            'privacy' => '개인정보처리방침',
            'terms' => '이용약관',
            default => '페이지',
        };

        return redirect()
            ->route('admin.pages.edit', $slug)
            ->with('success', "{$label}이(가) 저장되었습니다.");
    }
}
