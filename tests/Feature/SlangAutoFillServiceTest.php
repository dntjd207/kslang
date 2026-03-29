<?php

use App\Models\Category;
use App\Models\Slang;
use App\Services\GeminiResponse;
use App\Services\GeminiService;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('stores english usage context from gemini when auto filling a pending slang', function () {
    $category = Category::create([
        'name' => '인터넷 밈',
        'description' => '온라인 유행어',
        'sort_order' => 0,
    ]);

    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => '',
        'english_description' => '',
        'korean_description' => '',
        'level' => 1,
        'usage_frequency' => 'Occasional',
        'usage_context' => '',
        'english_usage_context' => '',
        'sort_order' => 0,
        'is_active' => false,
        'content_status' => Slang::STATUS_PENDING,
    ]);

    $generatedPayload = [
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term used when someone feels unfairly criticized or nitpicked.',
        'korean_description' => '상대가 억지로 트집을 잡거나 부당하게 비난한다고 느낄 때 쓰는 표현이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '주로 온라인 커뮤니티나 채팅에서 억울함을 표현할 때 사용된다.',
        'english_usage_context' => 'It is commonly used in online communities or chats to express that someone is being unfairly nitpicky.',
        'examples' => [
            [
                'korean_example' => '그건 좀 억까 아니냐?',
                'english_example' => 'Isn\'t that a bit of an unfair nitpick?',
            ],
            [
                'korean_example' => '댓글 분위기가 완전 억까네.',
                'english_example' => 'The comment section is full of unfair criticism.',
            ],
        ],
        'suggested_categories' => [$category->name],
    ];

    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode($generatedPayload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->mock(GeminiService::class, function (MockInterface $mock) use ($geminiResponse): void {
        $mock->shouldReceive('generate')
            ->once()
            ->withArgs(function (string $prompt, array $schema, string $thinkingLevel): bool {
                expect($prompt)->toContain('english_usage_context');
                expect(data_get($schema, 'properties.english_usage_context.type'))->toBe('STRING');
                expect(data_get($schema, 'required'))->toContain('english_usage_context');
                expect($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $filledSlang = app(SlangAutoFillService::class)->fillSlang($slang);

    expect($filledSlang->english_usage_context)->toBe($generatedPayload['english_usage_context'])
        ->and($filledSlang->usage_context)->toBe($generatedPayload['usage_context'])
        ->and($filledSlang->content_status)->toBe(Slang::STATUS_GENERATED)
        ->and($filledSlang->is_active)->toBeFalse()
        ->and($filledSlang->categories)->toHaveCount(1)
        ->and($filledSlang->categories->first()->is($category))->toBeTrue()
        ->and($filledSlang->examples)->toHaveCount(2);
});
