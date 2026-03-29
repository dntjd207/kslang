<?php

use App\Models\Slang;
use App\Models\User;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('returns regenerated descriptions for review without saving immediately', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);

    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'Original English description.',
        'korean_description' => '기존 한글 설명이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 쓴다.',
        'english_usage_context' => 'It is used online when someone feels unfairly judged.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $this->mock(SlangAutoFillService::class, function (MockInterface $mock) use ($slang): void {
        $mock->shouldReceive('regenerateDescriptions')
            ->once()
            ->withArgs(function (Slang $receivedSlang, array $payload) use ($slang): bool {
                expect($receivedSlang->is($slang))->toBeTrue()
                    ->and($payload['section'])->toBe('descriptions')
                    ->and($payload['korean'])->toBe('억까')
                    ->and($payload['usage_context'])->toBe('폼에서 수정한 사용 상황');

                return true;
            })
            ->andReturn([
                'english_description' => 'Regenerated English description.',
                'korean_description' => '재생성된 한글 설명이다.',
            ]);
    });

    $this->actingAs($user)
        ->postJson(route('admin.slangs.regenerateSection', $slang), [
            'section' => 'descriptions',
            'korean' => '억까',
            'pronunciation' => 'eok-kka',
            'english_description' => '폼에서 수정한 영어 설명',
            'korean_description' => '폼에서 수정한 한글 설명',
            'level' => 1,
            'usage_frequency' => 'Common',
            'usage_context' => '폼에서 수정한 사용 상황',
            'english_usage_context' => 'Edited English usage context in the form.',
            'examples' => [
                [
                    'korean_example' => '그건 좀 억까 아니냐?',
                    'english_example' => 'Isn\'t that a bit of an unfair nitpick?',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('section', 'descriptions')
        ->assertJsonPath('data.english_description', 'Regenerated English description.')
        ->assertJsonPath('data.korean_description', '재생성된 한글 설명이다.');

    $slang->refresh();

    expect($slang->english_description)->toBe('Original English description.')
        ->and($slang->korean_description)->toBe('기존 한글 설명이다.');
});

it('returns three additional examples without saving them immediately', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'editor@example.com',
        'login_id' => 'editor',
        'password' => 'password',
    ]);

    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'Original English description.',
        'korean_description' => '기존 한글 설명이다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 쓴다.',
        'english_usage_context' => 'It is used online when someone feels unfairly judged.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $this->mock(SlangAutoFillService::class, function (MockInterface $mock) use ($slang): void {
        $mock->shouldReceive('generateAdditionalExamples')
            ->once()
            ->withArgs(function (Slang $receivedSlang, array $payload, int $count) use ($slang): bool {
                expect($receivedSlang->is($slang))->toBeTrue()
                    ->and($payload['section'])->toBe('examples')
                    ->and($count)->toBe(3);

                return true;
            })
            ->andReturn([
                'examples' => [
                    [
                        'korean_example' => '오늘 댓글 반응 너무 억까다.',
                        'english_example' => 'The comments today are way too unfair.',
                    ],
                    [
                        'korean_example' => '그 장면만 보고 억까하는 거야.',
                        'english_example' => 'You are judging it unfairly based on one scene.',
                    ],
                    [
                        'korean_example' => '이건 진짜 억까 취급받을 만하네.',
                        'english_example' => 'This really does feel like unfair criticism.',
                    ],
                ],
            ]);
    });

    $this->actingAs($user)
        ->postJson(route('admin.slangs.regenerateSection', $slang), [
            'section' => 'examples',
            'korean' => '억까',
            'examples' => [
                [
                    'korean_example' => '기존 예문',
                    'english_example' => 'Existing example',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('section', 'examples')
        ->assertJsonCount(3, 'data.examples')
        ->assertJsonPath('data.examples.0.korean_example', '오늘 댓글 반응 너무 억까다.');

    expect($slang->examples()->count())->toBe(0);
});
