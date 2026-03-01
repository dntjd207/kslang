<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAppSettingRequest;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AppSettingController extends Controller
{
    private const SETTING_KEYS = [
        'min_version',
        'latest_version',
        'play_store_url',
    ];

    public function edit(): View
    {
        $settings = AppSetting::getMultiple(self::SETTING_KEYS);

        $lastUpdated = AppSetting::whereIn('key', self::SETTING_KEYS)
            ->max('updated_at');

        return view('admin.app-settings.edit', [
            'settings' => $settings,
            'lastUpdated' => $lastUpdated,
        ]);
    }

    public function update(UpdateAppSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        AppSetting::setValue('min_version', $validated['min_version']);
        AppSetting::setValue('latest_version', $validated['latest_version']);
        AppSetting::setValue('play_store_url', $validated['play_store_url'] ?? '');

        return redirect()
            ->route('admin.app-settings.edit')
            ->with('success', '앱 설정이 저장되었습니다.');
    }
}
