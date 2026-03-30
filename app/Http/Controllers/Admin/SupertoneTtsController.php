<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateSupertoneTtsRequest;
use App\Services\SupertoneTtsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SupertoneTtsController extends Controller
{
    public function __construct(
        private SupertoneTtsService $supertoneTtsService
    ) {}

    public function index(): View
    {
        return view('admin.supertone-tts.index', [
            'pageTitle' => 'Supertone TTS 테스트',
            'configuration' => [
                'base_url' => $this->supertoneTtsService->getBaseUrl(),
                'configured_voice_id' => $this->supertoneTtsService->getConfiguredVoiceId(),
                'has_configured_api_key' => $this->supertoneTtsService->hasConfiguredApiKey(),
                'masked_api_key' => $this->supertoneTtsService->getMaskedConfiguredApiKey(),
                'storage_disk' => $this->supertoneTtsService->getStorageDisk(),
                'storage_location' => $this->supertoneTtsService->getStorageLocation(),
                'uses_temporary_url' => $this->supertoneTtsService->usesTemporaryUrls(),
                'temporary_url_minutes' => $this->supertoneTtsService->getTemporaryUrlMinutes(),
            ],
            'defaultInput' => $this->supertoneTtsService->getDefaultInput(),
            'recentResults' => $this->supertoneTtsService->getRecentResults(),
        ]);
    }

    public function generate(GenerateSupertoneTtsRequest $request): JsonResponse
    {
        try {
            $result = $this->supertoneTtsService->generateAndStore($request->validated());

            return response()->json([
                'success' => true,
                'message' => '음성 생성과 mp3 저장이 완료되었습니다.',
                'result' => $result,
                'recent_results' => $this->supertoneTtsService->getRecentResults(),
            ]);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Supertone API 서버에 연결하지 못했습니다. 네트워크 상태와 서비스 주소를 확인해주세요.',
            ], 502);
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extractRequestExceptionMessage($e),
            ], $e->response?->status() ?? 502);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => '음성 생성 중 예상치 못한 오류가 발생했습니다.',
            ], 500);
        }
    }

    private function extractRequestExceptionMessage(RequestException $exception): string
    {
        $response = $exception->response;

        if ($response === null) {
            return 'Supertone API 요청에 실패했습니다.';
        }

        $decoded = json_decode($response->body(), true);

        if (is_array($decoded)) {
            $message = $decoded['message']
                ?? $decoded['error']
                ?? $decoded['detail']
                ?? data_get($decoded, 'error.message');

            if (is_string($message) && trim($message) !== '') {
                return trim($message);
            }
        }

        $body = trim($response->body());

        if ($body !== '') {
            return Str::limit($body, 300);
        }

        return 'Supertone API 요청에 실패했습니다.';
    }
}
