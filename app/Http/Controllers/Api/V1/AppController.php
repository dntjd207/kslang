<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function version(): JsonResponse
    {
        $settings = AppSetting::whereIn('key', ['min_version', 'latest_version'])
            ->pluck('value', 'key');

        return response()->json([
            'min_version' => $settings->get('min_version'),
            'latest_version' => $settings->get('latest_version'),
        ]);
    }
}
