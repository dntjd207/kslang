<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key')
            ?? throw new RuntimeException('GEMINI_API_KEY is not configured.');
        $this->model = config('services.gemini.model');
        $this->baseUrl = config('services.gemini.base_url');
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Gemini API에 프롬프트를 전송하고 응답 텍스트를 반환.
     *
     * @param  array<string, mixed>|null  $responseSchema  JSON 응답 스키마 (선택)
     *
     * @throws ConnectionException
     */
    public function generate(
        string $prompt,
        ?array $responseSchema = null,
        string $thinkingLevel = 'HIGH',
        ?string $model = null
    ): GeminiResponse {
        $payload = $this->buildPayload($prompt, $responseSchema, $thinkingLevel);
        $resolvedModel = $model ?? $this->model;

        $response = Http::timeout(120)
            ->withQueryParameters(['key' => $this->apiKey])
            ->post("{$this->baseUrl}/models/{$resolvedModel}:generateContent", $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Gemini API error [{$response->status()}]: {$response->body()}"
            );
        }

        return new GeminiResponse($response->json());
    }

    /**
     * Gemini API에 스트리밍 요청을 전송하고 전체 응답을 합쳐 반환.
     *
     * @param  array<string, mixed>|null  $responseSchema  JSON 응답 스키마 (선택)
     *
     * @throws ConnectionException
     */
    public function streamGenerate(
        string $prompt,
        ?array $responseSchema = null,
        string $thinkingLevel = 'HIGH',
        ?string $model = null
    ): GeminiResponse {
        $payload = $this->buildPayload($prompt, $responseSchema, $thinkingLevel);
        $resolvedModel = $model ?? $this->model;

        $response = Http::timeout(120)
            ->withQueryParameters([
                'key' => $this->apiKey,
                'alt' => 'sse',
            ])
            ->post("{$this->baseUrl}/models/{$resolvedModel}:streamGenerateContent", $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Gemini API error [{$response->status()}]: {$response->body()}"
            );
        }

        return GeminiResponse::fromStreamBody($response->body());
    }

    /**
     * @param  array<string, mixed>|null  $responseSchema
     * @return array<string, mixed>
     */
    private function buildPayload(string $prompt, ?array $responseSchema, string $thinkingLevel): array
    {
        $generationConfig = [
            'thinkingConfig' => [
                'thinkingLevel' => $thinkingLevel,
            ],
        ];

        if ($responseSchema !== null) {
            $generationConfig['responseMimeType'] = 'application/json';
            $generationConfig['responseSchema'] = $responseSchema;
        }

        return [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => $generationConfig,
        ];
    }
}
