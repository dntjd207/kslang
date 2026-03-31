<?php

namespace App\Services;

use App\Models\Slang;
use RuntimeException;

class SlangThreadContentService
{
    public const FORMAT_WORD_DROP = 'word_drop';

    public const FORMAT_DID_YOU_KNOW = 'did_you_know';

    public const FORMAT_KOREAN_VS_ENGLISH = 'korean_vs_english';

    public const FORMAT_QUIZ_POLL = 'quiz_poll';

    /**
     * @var array<int, string>
     */
    private const FORMAT_KEYS = [
        self::FORMAT_WORD_DROP,
        self::FORMAT_DID_YOU_KNOW,
        self::FORMAT_KOREAN_VS_ENGLISH,
        self::FORMAT_QUIZ_POLL,
    ];

    public function __construct(
        private GeminiService $geminiService
    ) {}

    public function generateAndStore(Slang $slang): Slang
    {
        $formats = $this->generateFormats($slang);

        $slang->update([
            'thread_post_formats' => $formats,
            'thread_post_generated_at' => now(),
        ]);

        return $slang->fresh() ?? $slang;
    }

    /**
     * @return array<string, array{content: string, reply: ?string}>
     */
    public function generateFormats(Slang $slang): array
    {
        $response = $this->geminiService->generate(
            $this->buildPrompt($slang),
            $this->buildResponseSchema(),
            'MEDIUM'
        );

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException("Gemini 응답을 JSON으로 파싱할 수 없습니다: {$response->text}");
        }

        return $this->normalizeFormats($data);
    }

    private function buildPrompt(Slang $slang): string
    {
        $aiHint = trim((string) $slang->ai_generation_hint);
        $examples = $this->formatExamplesForPrompt($slang);
        $flames = $this->buildFlameLevel($slang->level);
        $pronunciation = trim((string) $slang->pronunciation) !== '' ? $slang->pronunciation : 'unknown';
        $aiHintText = $aiHint !== '' ? $aiHint : '(없음)';

        return <<<PROMPT
당신은 KSLang 앱의 소셜 콘텐츠 에디터입니다.
아래 한국어 슬랭 정보를 바탕으로 Threads에 바로 올릴 수 있는 영어 중심의 게시글 4종을 JSON으로 작성해주세요.

## 슬랭 정보
- korean_word: {$slang->korean}
- pronunciation: {$pronunciation}
- english_description: {$slang->english_description}
- korean_description: {$slang->korean_description}
- usage_context: {$slang->usage_context}
- english_usage_context: {$slang->english_usage_context}
- level: {$slang->level} ({$slang->level_korean_label})
- recommended_flame_level: {$flames}
- ai_hint: {$aiHintText}

## 예문
{$examples}

## 공통 규칙
1. 영어 중심으로 작성하되 한국어 단어 표기는 원문 그대로 유지하세요.
2. 제공된 정보와 모순되는 의미를 만들지 마세요.
3. 각 format의 content는 줄바꿈이 포함된 최종 게시글 문자열이어야 합니다.
4. 실제로 공유하고 댓글이 달릴 만한 자연스러운 톤으로 작성하세요.
5. 해시태그는 넣지 마세요.
6. JSON 외의 텍스트는 절대 반환하지 마세요.

## Format A: word_drop
- 템플릿 구조를 최대한 그대로 사용하세요.
- 첫 줄은 정확히 `**{$slang->korean}** ({$pronunciation})`
- 줄 순서는 다음과 같아야 합니다.
What it literally means: ...
What Koreans actually mean: ...
When to use it: ...
Level of savagery: {$flames}

Would you say this to your Korean friend? 👇
- literal meaning은 직역 또는 표면적인 이미지 설명으로 짧게 작성하세요.
- reply는 빈 문자열로 두세요.

## Format B: did_you_know
- 첫 줄은 `Did you know Koreans have a word for ___?`
- 다음 줄 구조를 유지하세요.
{$slang->korean} ({$pronunciation})
→ [explanation]

It's the kind of word your Korean textbook will NEVER teach you.

Learn more → KSLang app 📲
- reply는 빈 문자열로 두세요.

## Format C: korean_vs_english
- 첫 줄은 정확히 `English has no word for this.`
- 다음 줄 구조를 유지하세요.
Koreans say "{$slang->korean}" when ___

It's {$pronunciation}, and once you know it,
you'll hear it everywhere in K-dramas.

What word does your language have for this? 👇
- reply는 빈 문자열로 두세요.

## Format D: quiz_poll
- 4지선다 퀴즈를 작성하세요.
- 첫 줄은 정확히 `Quick Korean slang quiz 🇰🇷`
- 문제는 `{$slang->korean}` 자체 또는 이 단어가 들어간 짧은 한국어 문장을 사용하세요.
- 보기 A, B, C, D는 각각 한 줄씩 작성하세요.
- 마지막 두 줄은 반드시 아래와 같아야 합니다.
Comment your answer 👇
(Answer in replies)
- reply에는 `Answer: X)` 형식으로 시작하는 짧은 정답 설명을 영어로 작성하세요.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResponseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                self::FORMAT_WORD_DROP => $this->buildFormatSchema('The Word Drop content'),
                self::FORMAT_DID_YOU_KNOW => $this->buildFormatSchema('Did You Know content'),
                self::FORMAT_KOREAN_VS_ENGLISH => $this->buildFormatSchema('Korean vs English content'),
                self::FORMAT_QUIZ_POLL => $this->buildFormatSchema('Quiz or poll content with reply answer'),
            ],
            'required' => self::FORMAT_KEYS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormatSchema(string $description): array
    {
        return [
            'type' => 'OBJECT',
            'description' => $description,
            'properties' => [
                'content' => [
                    'type' => 'STRING',
                    'description' => 'Final Threads post content with preserved line breaks',
                ],
                'reply' => [
                    'type' => 'STRING',
                    'description' => 'Optional follow-up reply text. Use empty string when not needed.',
                ],
            ],
            'required' => ['content', 'reply'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, array{content: string, reply: ?string}>
     */
    private function normalizeFormats(array $data): array
    {
        $formats = [];

        foreach (self::FORMAT_KEYS as $key) {
            $content = trim((string) data_get($data, "{$key}.content", ''));
            $reply = trim((string) data_get($data, "{$key}.reply", ''));

            if ($content === '') {
                throw new RuntimeException("Thread 포맷 {$key}의 content가 비어 있습니다.");
            }

            $formats[$key] = [
                'content' => $content,
                'reply' => $reply !== '' ? $reply : null,
            ];
        }

        return $formats;
    }

    private function buildFlameLevel(int $level): string
    {
        $clampedLevel = max(1, min(4, $level));

        return str_repeat('🔥', $clampedLevel);
    }

    private function formatExamplesForPrompt(Slang $slang): string
    {
        $examples = $slang->examples()
            ->get(['korean_example', 'english_example']);

        if ($examples->isEmpty()) {
            return '- (등록된 예문 없음)';
        }

        return $examples
            ->map(function (object $example, int $index): string {
                $number = $index + 1;

                return "{$number}. 한국어: {$example->korean_example} / 영어: {$example->english_example}";
            })
            ->implode("\n");
    }
}
