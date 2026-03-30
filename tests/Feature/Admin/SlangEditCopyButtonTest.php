<?php

use App\Models\Slang;
use App\Models\User;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->mock(SlangAutoFillService::class, function (MockInterface $mock): void {});
});

function slangEditAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function createEditableSlang(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '뇌절',
        'ai_generation_hint' => '밈을 너무 반복하는 상황',
        'pronunciation' => 'noe-jeol',
        'english_description' => 'It describes a joke or meme that gets repeated far beyond the right timing.',
        'korean_description' => '같은 밈이나 농담을 너무 오래 끌어서 분위기를 망치는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '인터넷 방송이나 채팅에서 자주 쓰인다.',
        'english_usage_context' => 'It is often used in streaming chats or online communities.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ], $attributes));
}

it('shows a card news copy button on the slang edit page', function () {
    $slang = createEditableSlang();
    $slang->examples()->create([
        'korean_example' => '그 밈 또 쓰면 진짜 뇌절이야.',
        'english_example' => 'If you use that meme again, it is seriously overdoing it.',
        'sort_order' => 0,
    ]);

    $this->actingAs(slangEditAdminUser())
        ->get(route('admin.slangs.edit', $slang))
        ->assertSuccessful()
        ->assertSee('카드뉴스용 복사')
        ->assertSee('현재 폼의 단어, 설명, 예문을 카드뉴스용 포맷으로 복사할 수 있습니다.')
        ->assertSee('data-copy-card-news', false)
        ->assertSee('그 밈 또 쓰면 진짜 뇌절이야.')
        ->assertSee('If you use that meme again, it is seriously overdoing it.');
});
