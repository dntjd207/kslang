<?php

use App\Models\Category;
use App\Models\Slang;
use App\Models\User;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->mock(SlangAutoFillService::class, function (MockInterface $mock): void {});
});

function slangIndexAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function createAdminSlang(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '기본 단어',
        'ai_generation_hint' => null,
        'pronunciation' => 'gi-bon',
        'english_description' => 'Default English description.',
        'korean_description' => '기본 한글 설명',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '기본 사용 상황',
        'english_usage_context' => 'Default usage context.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ], $attributes));
}

it('shows the full slang list on one page with category tab counts', function () {
    $memeCategory = Category::create([
        'name' => '밈',
        'description' => '밈 관련 표현',
        'sort_order' => 0,
    ]);

    $gameCategory = Category::create([
        'name' => '게임',
        'description' => '게임 관련 표현',
        'sort_order' => 1,
    ]);

    $lastSlang = null;

    foreach (range(1, 25) as $index) {
        $slang = createAdminSlang([
            'korean' => "단어 {$index}",
            'pronunciation' => "dan-eo-{$index}",
            'sort_order' => $index,
        ]);

        if ($index <= 21) {
            $slang->categories()->attach($memeCategory);
        }

        if ($index >= 16) {
            $slang->categories()->attach($gameCategory);
        }

        $lastSlang = $slang;
    }

    $this->actingAs(slangIndexAdminUser())
        ->get(route('admin.slangs.index'))
        ->assertSuccessful()
        ->assertSee('카테고리별 보기')
        ->assertSee('단어 25')
        ->assertViewHas('slangs', function ($slangs) use ($lastSlang): bool {
            return $slangs instanceof Collection
                && $slangs->count() === 25
                && $slangs->last()?->is($lastSlang);
        })
        ->assertViewHas('categories', function ($categories) use ($memeCategory, $gameCategory): bool {
            return $categories instanceof Collection
                && $categories->firstWhere('id', $memeCategory->id)?->filtered_slangs_count === 21
                && $categories->firstWhere('id', $gameCategory->id)?->filtered_slangs_count === 10;
        })
        ->assertViewHas('categoryTotalCount', 25)
        ->assertViewHas('isReorderable', true);
});

it('filters the slang list by category and disables drag sorting in filtered mode', function () {
    $internetCategory = Category::create([
        'name' => '인터넷',
        'description' => '온라인 표현',
        'sort_order' => 0,
    ]);

    $streamingCategory = Category::create([
        'name' => '방송',
        'description' => '방송 관련 표현',
        'sort_order' => 1,
    ]);

    $internetOnlySlang = createAdminSlang([
        'korean' => '인터넷 전용',
        'sort_order' => 0,
    ]);
    $internetOnlySlang->categories()->attach($internetCategory);

    $sharedSlang = createAdminSlang([
        'korean' => '공통 표현',
        'sort_order' => 1,
    ]);
    $sharedSlang->categories()->attach([$internetCategory->id, $streamingCategory->id]);

    $streamingOnlySlang = createAdminSlang([
        'korean' => '방송 전용',
        'sort_order' => 2,
    ]);
    $streamingOnlySlang->categories()->attach($streamingCategory);

    $this->actingAs(slangIndexAdminUser())
        ->get(route('admin.slangs.index', ['category_id' => $internetCategory->id]))
        ->assertSuccessful()
        ->assertSee('인터넷 전용')
        ->assertSee('공통 표현')
        ->assertDontSee('방송 전용')
        ->assertViewHas('slangs', function ($slangs) use ($internetOnlySlang, $sharedSlang): bool {
            return $slangs instanceof Collection
                && $slangs->modelKeys() === [$internetOnlySlang->id, $sharedSlang->id];
        })
        ->assertViewHas('categories', function ($categories) use ($internetCategory, $streamingCategory): bool {
            return $categories instanceof Collection
                && $categories->firstWhere('id', $internetCategory->id)?->filtered_slangs_count === 2
                && $categories->firstWhere('id', $streamingCategory->id)?->filtered_slangs_count === 2;
        })
        ->assertViewHas('categoryTotalCount', 3)
        ->assertViewHas('isReorderable', false);
});
