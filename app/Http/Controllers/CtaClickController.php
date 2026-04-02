<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackCtaClickRequest;
use App\Models\CtaClick;
use Illuminate\Http\JsonResponse;

class CtaClickController extends Controller
{
    public function store(TrackCtaClickRequest $request): JsonResponse
    {
        CtaClick::create([
            ...$request->validated(),
            'target' => $request->validated('target', 'google_play'),
            'referer_url' => $request->headers->get('referer'),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
