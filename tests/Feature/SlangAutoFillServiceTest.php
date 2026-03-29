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

it('regenerates descriptions without persisting them immediately', function () {
    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'Original English description.',
        'korean_description' => '기존 한글 설명이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 주로 쓴다.',
        'english_usage_context' => 'It is mainly used online when someone feels unfairly judged.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode([
                                'english_description' => 'A slang term used when someone complains about unfair criticism or forced nitpicking.',
                                'korean_description' => '상대가 억지로 트집을 잡거나 부당하게 몰아간다고 느낄 때 쓰는 표현이다.',
                            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
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
                expect($prompt)->toContain('current english_description: Original English description.')
                    ->and($prompt)->toContain('current usage_context: 온라인에서 억울함을 표현할 때 주로 쓴다.')
                    ->and(data_get($schema, 'required'))->toBe(['english_description', 'korean_description'])
                    ->and($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $result = app(SlangAutoFillService::class)->regenerateDescriptions($slang, [
        'english_description' => 'Original English description.',
        'korean_description' => '기존 한글 설명이다.',
        'usage_context' => '온라인에서 억울함을 표현할 때 주로 쓴다.',
        'english_usage_context' => 'It is mainly used online when someone feels unfairly judged.',
    ]);

    expect($result)->toBe([
        'english_description' => 'A slang term used when someone complains about unfair criticism or forced nitpicking.',
        'korean_description' => '상대가 억지로 트집을 잡거나 부당하게 몰아간다고 느낄 때 쓰는 표현이다.',
    ]);

    $slang->refresh();

    expect($slang->english_description)->toBe('Original English description.')
        ->and($slang->korean_description)->toBe('기존 한글 설명이다.');
});

it('regenerates usage context without persisting it immediately', function () {
    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '기존 사용 상황',
        'english_usage_context' => 'Original usage context in English.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode([
                                'usage_context' => '주로 커뮤니티 댓글이나 채팅에서 억울함을 강조할 때 사용된다.',
                                'english_usage_context' => 'It is commonly used in community comments or chats to emphasize that the criticism feels unfair.',
                            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
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
                expect($prompt)->toContain('english_description: A slang term for unfair criticism.')
                    ->and($prompt)->toContain('korean_description: 억지로 비난하거나 트집을 잡는 상황을 뜻한다.')
                    ->and(data_get($schema, 'required'))->toBe(['usage_context', 'english_usage_context'])
                    ->and($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $result = app(SlangAutoFillService::class)->regenerateUsageContext($slang, [
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
    ]);

    expect($result)->toBe([
        'usage_context' => '주로 커뮤니티 댓글이나 채팅에서 억울함을 강조할 때 사용된다.',
        'english_usage_context' => 'It is commonly used in community comments or chats to emphasize that the criticism feels unfair.',
    ]);

    $slang->refresh();

    expect($slang->usage_context)->toBe('기존 사용 상황')
        ->and($slang->english_usage_context)->toBe('Original usage context in English.');
});

it('generates three additional examples from current slang context', function () {
    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 사용된다.',
        'english_usage_context' => 'It is used online to express that criticism feels unfair.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $slang->examples()->createMany([
        [
            'korean_example' => '그건 좀 억까 아니냐?',
            'english_example' => 'Isn\'t that a bit of an unfair nitpick?',
            'sort_order' => 0,
        ],
        [
            'korean_example' => '댓글이 너무 억까 분위기야.',
            'english_example' => 'The comments feel way too unfairly hostile.',
            'sort_order' => 1,
        ],
    ]);

    $geminiResponse = new GeminiResponse([
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        [
                            'text' => json_encode([
                                'examples' => [
                                    [
                                        'korean_example' => '오늘 경기 평가 너무 억까로 흐르네.',
                                        'english_example' => 'The discussion about today\'s match is turning into unfair criticism.',
                                    ],
                                    [
                                        'korean_example' => '그 정도 실수로 욕먹는 건 좀 억까지.',
                                        'english_example' => 'Getting bashed for that small mistake feels pretty unfair.',
                                    ],
                                    [
                                        'korean_example' => '밈 하나 올렸다고 억까 당했어.',
                                        'english_example' => 'I got unfairly piled on just for posting a meme.',
                                    ],
                                ],
                            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
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
                expect($prompt)->toContain('기존 예문')
                    ->and($prompt)->toContain('그건 좀 억까 아니냐?')
                    ->and($prompt)->toContain('정확히 3개의 새로운 예문')
                    ->and(data_get($schema, 'properties.examples.type'))->toBe('ARRAY')
                    ->and($thinkingLevel)->toBe('MEDIUM');

                return true;
            })
            ->andReturn($geminiResponse);
    });

    $result = app(SlangAutoFillService::class)->generateAdditionalExamples($slang);

    expect($result['examples'])->toHaveCount(3)
        ->and($result['examples'][0]['korean_example'])->toBe('오늘 경기 평가 너무 억까로 흐르네.')
        ->and($result['examples'][2]['english_example'])->toBe('I got unfairly piled on just for posting a meme.');

    expect($slang->examples()->count())->toBe(2);
});
