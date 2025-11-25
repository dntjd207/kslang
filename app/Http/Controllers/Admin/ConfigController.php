<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AppConfig;

class ConfigController extends Controller
{
    public function index()
    {
        $configs = AppConfig::all()->keyBy('key');
        return view('admin.config.index', compact('configs'));
    }

    public function update(Request $request)
    {
        // Update version_code
        AppConfig::where('key', 'version_code')->update(['value' => $request->input('version_code')]);

        // Update maintenance_message
        AppConfig::where('key', 'maintenance_message')->update(['value' => $request->input('maintenance_message')]);

        // Update force_update
        $forceUpdate = $request->has('force_update') ? 'true' : 'false';
        AppConfig::where('key', 'force_update')->update(['value' => $forceUpdate]);

        return redirect()->back()->with('success', '설정이 성공적으로 업데이트되었습니다.');
    }
}

