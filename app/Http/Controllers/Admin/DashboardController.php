<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Word;
use App\AppConfig;

class DashboardController extends Controller
{
    public function index()
    {
        $wordCount = Word::count();
        $versionCode = AppConfig::where('key', 'version_code')->value('value');

        return view('admin.dashboard', compact('wordCount', 'versionCode'));
    }
}

