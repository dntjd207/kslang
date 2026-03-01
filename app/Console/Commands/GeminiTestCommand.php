<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;

class GeminiTestCommand extends Command
{
    protected $signature = 'gemini:test
        {prompt? : 전송할 프롬프트 텍스트}
        {--stream : 스트리밍 모드 사용}
        {--schema : 예시 responseSchema 적용}
        {--thinking= : thinkingLevel 설정 (NONE, LOW, MEDIUM, HIGH)}';

    protected $description = 'Gemini API 연동 테스트';

    public function handle(GeminiService $gemini): int
    {
        $prompt = $this->argument('prompt')
            ?? $this->ask('프롬프트를 입력하세요');

        if (! $prompt) {
            $this->error('프롬프트가 비어있습니다.');

            return self::FAILURE;
        }

        $thinkingLevel = $this->option('thinking') ?? 'HIGH';
        $schema = $this->option('schema') ? $this->exampleSchema() : null;
        $useStream = $this->option('stream');

        $this->info("모델: {$gemini->getModel()}");
        $this->info('모드: '.($useStream ? '스트리밍' : '일반'));
        $this->info("thinkingLevel: {$thinkingLevel}");
        $this->info('responseSchema: '.($schema ? 'O' : 'X'));
        $this->newLine();

        $this->info('요청 중...');

        try {
            $response = $useStream
                ? $gemini->streamGenerate($prompt, $schema, $thinkingLevel)
                : $gemini->generate($prompt, $schema, $thinkingLevel);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($response->thinkingText) {
            $this->newLine();
            $this->comment('--- Thinking ---');
            $this->line($response->thinkingText);
            $this->comment('--- /Thinking ---');
        }

        $this->newLine();
        $this->comment('--- Response ---');
        $this->line($response->text);
        $this->comment('--- /Response ---');

        if ($schema && $decoded = $response->json()) {
            $this->newLine();
            $this->comment('--- Parsed JSON ---');
            $this->line(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->comment('--- /Parsed JSON ---');
        }

        $this->newLine();
        $this->info('완료.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function exampleSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'korean_word' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['korean_word'],
            'propertyOrdering' => ['korean_word'],
        ];
    }
}
