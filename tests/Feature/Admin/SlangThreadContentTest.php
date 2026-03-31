<?php

use App\Models\Slang;
use App\Models\User;
use App\Services\GeminiResponse;
use App\Services\GeminiService;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(SlangAutoFillService::class, function (MockInterface $mock): void {});
    $this->mock(GeminiService::class, function (MockInterface $mock): void {});
});

function slangThreadContentAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function createAdminThreadContentSlang(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '뇌절',
        'ai_generation_hint' => '밈이나 농담을 너무 과하게 반복할 때 쓰는 표현이다.',
        'pronunciation' => 'noe-jeol',
        'english_description' => 'It means a joke has gone too far.',
        'korean_description' => '농담이 과해졌을 때 쓰는 표현이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '친구끼리 장난을 과하게 칠 때 자주 쓴다.',
        'english_usage_context' => 'It is often used when friends overdo a joke.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ], $attributes));
}

function adminThreadContentFormats(): array
{
    return [
        'word_drop' => [
            'content' => "**뇌절** (noe-jeol)\n\nWhat it literally means: taking it too far\nWhat Koreans actually mean: overdoing a joke until it stops being funny\nWhen to use it: when a meme or bit keeps dragging on\nLevel of savagery: 🔥\n\nWould you say this to your Korean friend? 👇",
            'reply' => null,
        ],
        'did_you_know' => [
            'content' => "Did you know Koreans have a word for dragging a joke way too long?\n\n뇌절 (noe-jeol)\n→ It is what people say when a joke, meme, or bit goes too far.\n\nIt's the kind of word your Korean textbook will NEVER teach you.\n\nLearn more → KSLang app 📲",
            'reply' => null,
        ],
        'korean_vs_english' => [
            'content' => "English has no word for this.\n\nKoreans say \"뇌절\" when someone keeps forcing the same joke long after it stopped landing.\n\nIt's noe-jeol, and once you know it,\nyou'll hear it everywhere in K-dramas.\n\nWhat word does your language have for this? 👇",
            'reply' => null,
        ],
        'quiz_poll' => [
            'content' => "Quick Korean slang quiz 🇰🇷\n\nIf someone says \"그 밈은 이제 뇌절이야,\" they mean:\n\nA) That meme is still getting better\nB) That meme is now overdone\nC) That meme is hard to translate\nD) That meme is very smart\n\nComment your answer 👇\n(Answer in replies)",
            'reply' => 'Answer: B) It means the meme or joke is now overdone and killing the vibe.',
        ],
    ];
}

it('generates and returns stored thread content formats from the admin endpoint', function () {
    $slang = createAdminThreadContentSlang();
    $formats = adminThreadContentFormats();
    $generatedAt = now()->startOfMinute();

    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode($formats, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
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
                expect($prompt)->toContain($slang->korean)
                    ->and($prompt)->toContain('Format D: quiz_poll');
                expect(data_get($schema, 'properties.quiz_poll.properties.reply.type'))->toBe('STRING')
                    ->and($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $this->actingAs(slangThreadContentAdminUser())
        ->postJson(route('admin.slangs.threadPosts.generate', $slang))
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Thread 콘텐츠 4종을 생성하고 저장했습니다.')
        ->assertJsonPath('data.slang_id', $slang->id)
        ->assertJsonPath('data.has_saved_formats', true)
        ->assertJsonPath('data.formats.word_drop.content', $formats['word_drop']['content'])
        ->assertJsonPath('data.formats.quiz_poll.reply', $formats['quiz_poll']['reply']);

    $slang->refresh();

    expect($slang->thread_post_generated_at)->not->toBeNull()
        ->and($slang->thread_post_generated_at?->format('Y-m-d H:i'))->toBe($generatedAt->format('Y-m-d H:i'));
});

it('returns saved thread content formats for later reuse', function () {
    $formats = adminThreadContentFormats();
    $generatedAt = now()->startOfMinute();
    $slang = createAdminThreadContentSlang([
        'thread_post_formats' => $formats,
        'thread_post_generated_at' => $generatedAt,
    ]);

    $this->actingAs(slangThreadContentAdminUser())
        ->getJson(route('admin.slangs.threadPosts.show', $slang))
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', '저장된 Thread 콘텐츠를 불러왔습니다.')
        ->assertJsonPath('data.has_saved_formats', true)
        ->assertJsonPath('data.generated_at', $generatedAt->format('Y-m-d H:i'))
        ->assertJsonPath('data.formats.did_you_know.content', $formats['did_you_know']['content']);
});

it('returns not found when no saved thread content exists yet', function () {
    $slang = createAdminThreadContentSlang();

    $this->actingAs(slangThreadContentAdminUser())
        ->getJson(route('admin.slangs.threadPosts.show', $slang))
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', '저장된 Thread 콘텐츠가 없습니다. 먼저 생성해주세요.');
});
