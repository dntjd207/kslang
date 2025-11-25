<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AppConfig;

class VersionController extends Controller
{
    public function index()
    {
        $configs = AppConfig::all()->pluck('value', 'key');

        return response()->json([
            'version_code' => (int) ($configs['version_code'] ?? 0),
            'force_update' => filter_var($configs['force_update'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'maintenance_message' => $configs['maintenance_message'] ?? null,
        ]);
    }
}

