<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Slang;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function version(): JsonResponse
    {
        $settings = AppSetting::whereIn('key', ['min_version', 'latest_version', 'play_store_url'])
            ->pluck('value', 'key');

        return response()->json([
            'min_version' => $settings->get('min_version'),
            'latest_version' => $settings->get('latest_version'),
            'play_store_url' => $settings->get('play_store_url'),
        ]);
    }

    public function sync(): JsonResponse
    {
        return response()->json([
            'slangs' => [
                'total_count' => Slang::apiVisible()->count(),
                'last_updated_at' => Slang::apiVisible()->max('updated_at'),
            ],
            'categories' => [
                'total_count' => Category::count(),
                'last_updated_at' => Category::max('updated_at'),
            ],
        ]);
    }
}
