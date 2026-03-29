<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExecuteApiPlaygroundRequest;
use App\Services\ApiPlaygroundService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Throwable;

class ApiPlaygroundController extends Controller
{
    public function __construct(
        private ApiPlaygroundService $apiPlaygroundService
    ) {}

    public function index(): View
    {
        $endpoints = $this->apiPlaygroundService->getEndpoints();

        return view('admin.api-playground.index', [
            'pageTitle' => 'API 요청 테스트',
            'baseUrl' => url('/api/v1'),
            'endpoints' => $endpoints,
            'endpointCount' => count($endpoints),
        ]);
    }

    public function execute(ExecuteApiPlaygroundRequest $request): JsonResponse
    {
        $endpoint = $this->apiPlaygroundService->findEndpoint($request->validated('endpoint_key'));

        if ($endpoint === null) {
            return response()->json([
                'success' => false,
                'message' => '허용되지 않은 API 엔드포인트입니다.',
            ], 422);
        }

        try {
            $result = $this->apiPlaygroundService->execute(
                endpoint: $endpoint,
                pathParams: $request->pathParameters(),
                queryParams: $request->queryParameters(),
                baseUrl: $request->getSchemeAndHttpHost()
            );

            return response()->json([
                'success' => true,
                ...$result,
            ]);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'API 서버에 연결하지 못했습니다. 현재 접속 중인 도메인과 Laravel Herd 상태를 확인해주세요.',
            ], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'API 요청 처리 중 예상치 못한 오류가 발생했습니다.',
            ], 500);
        }
    }
}
