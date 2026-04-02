<?php

use App\Models\AppSetting;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Slang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function publicSlangCategory(): Category
{
    return Category::create([
        'name' => '인터넷 밈',
        'description' => '온라인 밈 관련 표현',
        'sort_order' => 0,
    ]);
}

function publicSlangEntry(array $overrides = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '부당하게 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 자주 쓴다.',
        'english_usage_context' => 'It is often used online when someone feels unfairly judged.',
        'public_slug' => 'eok-kka',
        'public_title_en' => 'What does 억까 mean in Korean?',
        'public_summary_en' => 'A quick guide to the Korean slang term 억까.',
        'seo_title_en' => 'What Does 억까 Mean in Korean?',
        'seo_description_en' => 'Learn what 억까 means in Korean, when it is used, and what nuance it carries.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_APPROVED,
        'approved_at' => now()->subDay(),
    ], $overrides));
}

it('shows official landing download ctas when no custom play store setting exists', function () {
    $this->get(route('landing'))
        ->assertSuccessful()
        ->assertSee(AppSetting::DEFAULT_PLAY_STORE_URL)
        ->assertSee('data-cta-source-type="site_nav"', false)
        ->assertSee('data-cta-source-type="landing"', false);
});

it('shows the public slang detail page with examples and related blog posts', function () {
    $category = publicSlangCategory();
    $slang = publicSlangEntry();
    $slang->categories()->sync([$category->id]);
    $slang->examples()->createMany([
        [
            'korean_example' => '그건 좀 억까 아니냐?',
            'english_example' => 'Isn\'t that unfair criticism?',
            'sort_order' => 0,
        ],
        [
            'korean_example' => '댓글이 너무 억까다.',
            'english_example' => 'The comments are way too unfair.',
            'sort_order' => 1,
        ],
    ]);

    $blogPost = BlogPost::factory()->published()->create([
        'slug' => 'what-does-eok-kka-mean-in-korean',
        'title_en' => 'What Does 억까 Mean in Korean?',
        'excerpt_en' => 'A guide to the Korean slang term 억까.',
    ]);
    $blogPost->slangs()->sync([$slang->id]);

    $response = $this->get(route('slangs.public.show', ['slang' => $slang->public_slug]));

    $response
        ->assertSuccessful()
        ->assertSee('What does 억까 mean in Korean?')
        ->assertSee('A slang term for unfair criticism.')
        ->assertDontSee('Quick facts')
        ->assertSee('그건 좀 억까 아니냐?')
        ->assertSee('What Does 억까 Mean in Korean?');
});

it('shows ai generated faq items on slang detail page when available', function () {
    $faqItems = [
        ['question' => 'What does 억까 mean literally?', 'answer' => 'It roughly translates to unfair criticism or nitpicking.'],
        ['question' => 'Is 억까 offensive?', 'answer' => 'It is mild slang and generally not considered offensive.'],
    ];

    $slang = publicSlangEntry([
        'faq_items' => $faqItems,
    ]);

    $response = $this->get(route('slangs.public.show', ['slang' => $slang->public_slug]));

    $response
        ->assertSuccessful()
        ->assertSee('What does 억까 mean literally?')
        ->assertSee('It roughly translates to unfair criticism or nitpicking.')
        ->assertSee('Is 억까 offensive?')
        ->assertSee('"@type": "FAQPage"', false);
});

it('hides faq section when faq_items is null', function () {
    $slang = publicSlangEntry([
        'faq_items' => null,
    ]);

    $response = $this->get(route('slangs.public.show', ['slang' => $slang->public_slug]));

    $response
        ->assertSuccessful()
        ->assertDontSee('"@type": "FAQPage"', false);
});

it('shows public slang entries on the dictionary index and hides non-public entries', function () {
    publicSlangEntry([
        'public_slug' => 'eok-kka',
        'korean' => '억까',
    ]);

    publicSlangEntry([
        'public_slug' => 'jjal',
        'korean' => '짤',
        'pronunciation' => 'jjal',
        'english_description' => 'An image or meme snippet.',
        'korean_description' => '밈 이미지나 짧은 이미지를 뜻한다.',
    ]);

    Slang::create([
        'korean' => '비공개',
        'pronunciation' => 'bi-gong-gae',
        'english_description' => 'Hidden entry',
        'korean_description' => '숨겨진 항목',
        'level' => 1,
        'usage_frequency' => 'Rare',
        'usage_context' => '사용하지 않음',
        'english_usage_context' => 'Hidden usage context',
        'public_slug' => 'hidden-entry',
        'sort_order' => 10,
        'is_active' => false,
        'content_status' => Slang::STATUS_PENDING,
    ]);

    $this->get(route('slangs.public.index'))
        ->assertSuccessful()
        ->assertSee('억까')
        ->assertSee('짤')
        ->assertDontSee('비공개');
});
