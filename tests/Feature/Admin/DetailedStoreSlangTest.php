<?php

use App\Models\Slang;
use App\Models\User;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(SlangAutoFillService::class, function (MockInterface $mock): void {});
});

function detailedStoreAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

it('stores a pending slang with an admin provided ai hint', function () {
    $this->actingAs(detailedStoreAdminUser())
        ->postJson(route('admin.slangs.detailedStore'), [
            'korean' => '뇌절',
            'ai_generation_hint' => '밈이나 농담을 너무 과하게 반복해서 분위기를 망칠 때 쓰는 유행어다.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', "'뇌절' 단어가 설명과 함께 등록되었습니다.");

    $slang = Slang::query()->first();

    expect($slang)->not->toBeNull()
        ->and($slang->korean)->toBe('뇌절')
        ->and($slang->ai_generation_hint)->toBe('밈이나 농담을 너무 과하게 반복해서 분위기를 망칠 때 쓰는 유행어다.')
        ->and($slang->content_status)->toBe(Slang::STATUS_PENDING)
        ->and($slang->is_active)->toBeFalse()
        ->and($slang->is_new)->toBeFalse()
        ->and($slang->approved_at)->toBeNull()
        ->and($slang->pronunciation)->toBe('')
        ->and($slang->korean_description)->toBe('');
});

it('rejects detailed registration for a duplicate word', function () {
    Slang::create([
        'korean' => '뇌절',
        'ai_generation_hint' => null,
        'pronunciation' => 'noe-jeol',
        'english_description' => 'Existing description',
        'korean_description' => '기존 설명',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '기존 사용 상황',
        'english_usage_context' => 'Existing usage context',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $this->actingAs(detailedStoreAdminUser())
        ->postJson(route('admin.slangs.detailedStore'), [
            'korean' => '뇌절',
            'ai_generation_hint' => '새로 설명을 달아도 이미 등록된 단어다.',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', "'뇌절' 단어는 이미 등록되어 있습니다.");

    expect(Slang::query()->count())->toBe(1);
});
