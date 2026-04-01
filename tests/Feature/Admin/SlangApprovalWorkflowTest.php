<?php

use App\Models\Slang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function slangApprovalAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function createGeneratedSlangForApproval(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '주로 온라인에서 사용된다.',
        'english_usage_context' => 'It is mainly used online.',
        'sort_order' => 0,
        'is_active' => false,
        'content_status' => Slang::STATUS_GENERATED,
        'is_new' => false,
        'approved_at' => null,
    ], $attributes));
}

it('marks a generated slang as new and stores the approval timestamp', function () {
    $slang = createGeneratedSlangForApproval();

    $this->actingAs(slangApprovalAdminUser())
        ->patchJson(route('admin.slangs.approve', $slang))
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', "'{$slang->korean}' 콘텐츠가 승인되었습니다.");

    $slang->refresh();

    expect($slang->content_status)->toBe(Slang::STATUS_APPROVED)
        ->and($slang->is_active)->toBeTrue()
        ->and($slang->is_new)->toBeTrue()
        ->and($slang->approved_at)->not->toBeNull();
});

it('clears is_new for slangs approved at least three days ago', function () {
    $expiredSlang = createGeneratedSlangForApproval([
        'korean' => '오래된 신규 단어',
        'content_status' => Slang::STATUS_APPROVED,
        'is_active' => true,
        'is_new' => true,
        'approved_at' => now()->subDays(3),
    ]);

    $recentSlang = createGeneratedSlangForApproval([
        'korean' => '최근 신규 단어',
        'sort_order' => 1,
        'content_status' => Slang::STATUS_APPROVED,
        'is_active' => true,
        'is_new' => true,
        'approved_at' => now()->subDays(2),
    ]);

    $this->artisan('slang:expire-new', ['--days' => 3])
        ->assertExitCode(0);

    expect($expiredSlang->fresh()->is_new)->toBeFalse()
        ->and($recentSlang->fresh()->is_new)->toBeTrue();
});
