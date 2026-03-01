<?php

namespace App\Services;

class GeminiResponse
{
    /** @var array<string, mixed> */
    public readonly array $raw;

    public readonly string $text;

    public readonly ?string $thinkingText;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->raw = $data;
        [$this->text, $this->thinkingText] = self::extractParts($data);
    }

    /**
     * SSE 스트리밍 응답 본문에서 JSON 청크들을 합쳐 GeminiResponse를 생성.
     */
    public static function fromStreamBody(string $body): self
    {
        $merged = ['candidates' => [['content' => ['parts' => []]]]];

        foreach (self::parseSSE($body) as $chunk) {
            $parts = data_get($chunk, 'candidates.0.content.parts', []);
            foreach ($parts as $part) {
                $merged['candidates'][0]['content']['parts'][] = $part;
            }

            if ($meta = data_get($chunk, 'usageMetadata')) {
                $merged['usageMetadata'] = $meta;
            }
        }

        return new self($merged);
    }

    /**
     * JSON 디코딩을 시도. responseSchema 사용 시 text가 JSON 문자열이므로 편의 메서드 제공.
     *
     * @return array<string, mixed>|null
     */
    public function json(): ?array
    {
        $decoded = json_decode($this->text, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private static function extractParts(array $data): array
    {
        $parts = data_get($data, 'candidates.0.content.parts', []);

        $textParts = [];
        $thinkingParts = [];

        foreach ($parts as $part) {
            if (isset($part['thought']) && $part['thought'] === true) {
                $thinkingParts[] = $part['text'] ?? '';
            } elseif (isset($part['text'])) {
                $textParts[] = $part['text'];
            }
        }

        return [
            implode('', $textParts),
            $thinkingParts !== [] ? implode('', $thinkingParts) : null,
        ];
    }

    /**
     * SSE 본문을 파싱하여 각 JSON 청크를 yield.
     *
     * @return \Generator<array<string, mixed>>
     */
    private static function parseSSE(string $body): \Generator
    {
        foreach (explode("\n", $body) as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'data: ')) {
                $json = substr($line, 6);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    yield $decoded;
                }
            }
        }
    }
}
