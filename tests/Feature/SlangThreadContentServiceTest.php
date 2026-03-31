<?php

use App\Models\Slang;
use App\Services\GeminiResponse;
use App\Services\GeminiService;
use App\Services\SlangService;
use App\Services\SlangThreadContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

function createThreadContentSlang(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '뇌절',
        'ai_generation_hint' => '밈이나 농담을 너무 오래 끌어서 분위기를 망칠 때 쓰는 표현이다.',
        'pronunciation' => 'noe-jeol',
        'english_description' => 'It describes taking a joke or meme way too far past the right moment.',
        'korean_description' => '같은 밈이나 농담을 과하게 반복해 분위기를 식게 만드는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '친구끼리 장난이 과해지거나 인터넷 방송에서 같은 드립을 반복할 때 자주 쓴다.',
        'english_usage_context' => 'It is often used when a joke keeps going too long among friends or in live streams.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ], $attributes));
}

function sampleThreadFormats(): array
{
    return [
        'word_drop' => [
            'content' => implode("\n", [
                '**뇌절** (noe-jeol)',
                '',
                'What it literally means: going so far your brain gets cut off',
                'What Koreans actually mean: taking a joke way too far until it stops being funny',
                'When to use it: when someone keeps repeating the same bit and kills the mood',
                'Level of savagery: 🔥',
                '',
                'Would you say this to your Korean friend? 👇',
            ]),
            'reply' => '',
        ],
        'did_you_know' => [
            'content' => implode("\n", [
                'Did you know Koreans have a word for overdoing a joke until everyone is tired of it?',
                '',
                '뇌절 (noe-jeol)',
                '→ It means a joke, meme, or bit has gone way past the point where it was funny.',
                '',
                'It\'s the kind of word your Korean textbook will NEVER teach you.',
                '',
                'Learn more → KSLang app 📲',
            ]),
            'reply' => '',
        ],
        'korean_vs_english' => [
            'content' => implode("\n", [
                'English has no word for this.',
                '',
                'Koreans say "뇌절" when a joke or meme keeps going until it becomes embarrassing.',
                '',
                'It\'s noe-jeol, and once you know it,',
                'you\'ll hear it everywhere in K-dramas.',
                '',
                'What word does your language have for this? 👇',
            ]),
            'reply' => '',
        ],
        'quiz_poll' => [
            'content' => implode("\n", [
                'Quick Korean slang quiz 🇰🇷',
                '',
                'If your friend says "그 드립 이제 뇌절이야," they mean:',
                '',
                'A) That joke is getting old',
                'B) That joke is getting funnier',
                'C) The joke is hard to understand',
                'D) The joke sounds smart',
                '',
                'Comment your answer 👇',
                '(Answer in replies)',
            ]),
            'reply' => 'Answer: A) It means the joke has gone too far and is no longer funny.',
        ],
    ];
}

it('generates and stores four thread content formats for a slang', function () {
    $slang = createThreadContentSlang();
    $slang->examples()->create([
        'korean_example' => '그 밈 또 하면 진짜 뇌절이야.',
        'english_example' => 'If you use that meme again, it is seriously overdoing it.',
        'sort_order' => 0,
    ]);

    $payload = sampleThreadFormats();
    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->mock(GeminiService::class, function (MockInterface $mock) use ($geminiResponse, $slang): void {
        $mock->shouldReceive('generate')
            ->once()
            ->withArgs(function (string $prompt, array $schema, string $thinkingLevel) use ($slang): bool {
                expect($prompt)->toContain('Format A: word_drop')
                    ->and($prompt)->toContain($slang->korean)
                    ->and($prompt)->toContain('Quick Korean slang quiz 🇰🇷')
                    ->and($prompt)->toContain('그 밈 또 하면 진짜 뇌절이야.');
                expect(data_get($schema, 'properties.word_drop.properties.content.type'))->toBe('STRING')
                    ->and(data_get($schema, 'properties.quiz_poll.properties.reply.type'))->toBe('STRING')
                    ->and(data_get($schema, 'required'))->toContain('quiz_poll')
                    ->and($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $generatedSlang = app(SlangThreadContentService::class)->generateAndStore($slang);

    expect($generatedSlang->thread_post_generated_at)->not->toBeNull()
        ->and(data_get($generatedSlang->thread_post_formats, 'word_drop.content'))->toContain('**뇌절**')
        ->and(data_get($generatedSlang->thread_post_formats, 'did_you_know.content'))->toContain('Did you know Koreans have a word')
        ->and(data_get($generatedSlang->thread_post_formats, 'quiz_poll.reply'))->toBe('Answer: A) It means the joke has gone too far and is no longer funny.');
});

it('clears saved thread content when the source slang content changes', function () {
    $slang = createThreadContentSlang([
        'thread_post_formats' => sampleThreadFormats(),
        'thread_post_generated_at' => now(),
    ]);

    $example = $slang->examples()->create([
        'korean_example' => '그건 진짜 뇌절이야.',
        'english_example' => 'That is seriously overdoing it.',
        'sort_order' => 0,
    ]);

    $updatedSlang = app(SlangService::class)->update($slang, [
        'korean' => '뇌절',
        'ai_generation_hint' => '밈이나 농담을 너무 오래 끌어 분위기를 망칠 때 쓰는 표현이다.',
        'pronunciation' => 'noe-jeol',
        'english_description' => 'Updated English description for thread invalidation.',
        'korean_description' => '업데이트된 한글 설명이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '업데이트된 사용 상황',
        'english_usage_context' => 'Updated English usage context.',
        'is_active' => true,
        'category_ids' => [],
        'examples' => [
            [
                'id' => $example->id,
                'korean_example' => '그건 진짜 뇌절이야.',
                'english_example' => 'That is seriously overdoing it.',
                'audio_file' => null,
                'audio_disk' => null,
            ],
        ],
    ]);

    $updatedSlang->refresh();

    expect($updatedSlang->thread_post_formats)->toBeNull()
        ->and($updatedSlang->thread_post_generated_at)->toBeNull();
});
