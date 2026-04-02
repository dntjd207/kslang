<?php

use App\Models\BlogPost;
use App\Models\Slang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function blogAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function publicBlogSlang(array $overrides = []): Slang
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
        'is_new' => false,
        'approved_at' => now()->subDay(),
    ], $overrides));
}

it('shows the admin blog index page to authenticated admins', function () {
    $this->actingAs(blogAdminUser())
        ->get(route('admin.blog-posts.index'))
        ->assertSuccessful()
        ->assertSee('블로그 글 관리');
});

it('stores a draft blog post and syncs related public slangs', function () {
    $slang = publicBlogSlang();

    $this->actingAs(blogAdminUser())
        ->post(route('admin.blog-posts.store'), [
            'save_action' => 'draft',
            'category_name' => 'Meaning',
            'tag_names' => 'korean slang, internet slang',
            'primary_keyword' => 'what does eok-kka mean in korean',
            'search_intent' => 'meaning',
            'content_brief_ko' => '영어권 사용자를 위한 뜻 설명형 글이다.',
            'title_ko' => '억까 뜻과 뉘앙스 정리',
            'excerpt_ko' => '억까의 뜻과 쓰임을 간단히 정리한다.',
            'body_ko' => '<h2>뜻</h2><p>억까는 억지로 트집을 잡는 상황을 뜻한다.</p>',
            'related_slang_ids' => [$slang->id],
        ])
        ->assertRedirect();

    $blogPost = BlogPost::query()->first();

    expect($blogPost)->not->toBeNull()
        ->and($blogPost->status)->toBe(BlogPost::STATUS_DRAFT)
        ->and($blogPost->translation_status)->toBe(BlogPost::TRANSLATION_NONE)
        ->and($blogPost->category_name)->toBe('Meaning')
        ->and($blogPost->tag_names)->toBe('korean slang, internet slang')
        ->and($blogPost->slug)->toBe('what-does-eok-kka-mean-in-korean')
        ->and($blogPost->slangs)->toHaveCount(1)
        ->and($blogPost->slangs->first()->is($slang))->toBeTrue();
});

it('publishes a blog post when english fields are ready', function () {
    $this->actingAs(blogAdminUser())
        ->post(route('admin.blog-posts.store'), [
            'save_action' => 'publish',
            'slug' => 'what-does-eok-kka-mean-in-korean',
            'search_intent' => 'meaning',
            'primary_keyword' => 'what does eok-kka mean in korean',
            'content_brief_ko' => '뜻 설명형 글',
            'title_ko' => '억까 뜻과 뉘앙스 정리',
            'excerpt_ko' => '억까의 뜻을 설명한다.',
            'body_ko' => '<h2>뜻</h2><p>억까는 억지 비난을 뜻한다.</p>',
            'title_en' => 'What Does 억까 Mean in Korean?',
            'excerpt_en' => 'A quick explanation of the Korean slang term 억까.',
            'body_en' => '<h2>Meaning</h2><p>억까 is a slang term for unfair criticism.</p>',
            'seo_title_en' => 'What Does 억까 Mean in Korean?',
            'seo_description_en' => 'Learn what 억까 means in Korean and when people use it.',
            'translation_model' => 'gemini-3.1-flash-lite-preview',
        ])
        ->assertRedirect();

    $blogPost = BlogPost::query()->first();

    expect($blogPost)->not->toBeNull()
        ->and($blogPost->status)->toBe(BlogPost::STATUS_PUBLISHED)
        ->and($blogPost->translation_status)->toBe(BlogPost::TRANSLATION_SYNCED)
        ->and($blogPost->published_at)->not->toBeNull()
        ->and($blogPost->en_synced_at)->not->toBeNull()
        ->and($blogPost->translation_model)->toBe('gemini-3.1-flash-lite-preview');
});

it('marks english content as outdated when the korean source changes without a matching english update', function () {
    $blogPost = BlogPost::factory()->published()->create([
        'slug' => 'what-does-eok-kka-mean-in-korean',
        'title_ko' => '기존 한국어 제목',
        'excerpt_ko' => '기존 요약',
        'body_ko' => '<h2>뜻</h2><p>기존 한국어 본문</p>',
        'title_en' => 'Original English Title',
        'excerpt_en' => 'Original English excerpt.',
        'body_en' => '<h2>Meaning</h2><p>Original English body.</p>',
    ]);

    $this->actingAs(blogAdminUser())
        ->put(route('admin.blog-posts.update', $blogPost), [
            'save_action' => 'draft',
            'slug' => $blogPost->slug,
            'search_intent' => $blogPost->search_intent,
            'primary_keyword' => $blogPost->primary_keyword,
            'content_brief_ko' => $blogPost->content_brief_ko,
            'title_ko' => '바뀐 한국어 제목',
            'excerpt_ko' => $blogPost->excerpt_ko,
            'body_ko' => '<h2>뜻</h2><p>수정된 한국어 본문</p>',
            'title_en' => $blogPost->title_en,
            'excerpt_en' => $blogPost->excerpt_en,
            'body_en' => $blogPost->body_en,
            'seo_title_en' => $blogPost->seo_title_en,
            'seo_description_en' => $blogPost->seo_description_en,
        ])
        ->assertRedirect(route('admin.blog-posts.edit', $blogPost));

    $blogPost->refresh();

    expect($blogPost->title_ko)->toBe('바뀐 한국어 제목')
        ->and($blogPost->status)->toBe(BlogPost::STATUS_DRAFT)
        ->and($blogPost->translation_status)->toBe(BlogPost::TRANSLATION_OUTDATED);
});

it('creates and updates a draft through the autosave endpoint', function () {
    $user = blogAdminUser();
    $slang = publicBlogSlang();

    $createResponse = $this->actingAs($user)
        ->postJson(route('admin.blog-posts.autosave'), [
            'category_name' => 'Usage',
            'tag_names' => 'korean slang, texting',
            'primary_keyword' => 'how to use eok-kka',
            'title_ko' => '억까 사용법 정리',
            'body_ko' => '<h2>사용법</h2><p>초안 본문</p>',
            'related_slang_ids' => [$slang->id],
        ]);

    $createResponse
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', BlogPost::STATUS_DRAFT);

    $blogPostId = $createResponse->json('data.blog_post_id');

    expect($blogPostId)->not->toBeNull();

    $this->actingAs($user)
        ->postJson(route('admin.blog-posts.autosave'), [
            'blog_post_id' => $blogPostId,
            'category_name' => 'Usage',
            'tag_names' => 'korean slang, texting, nuance',
            'primary_keyword' => 'how to use eok-kka',
            'title_ko' => '억까 사용법 정리',
            'body_ko' => '<h2>사용법</h2><p>업데이트된 초안 본문</p>',
            'related_slang_ids' => [$slang->id],
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.blog_post_id', $blogPostId);

    $blogPost = BlogPost::query()->findOrFail($blogPostId);

    expect($blogPost->category_name)->toBe('Usage')
        ->and($blogPost->tag_names)->toBe('korean slang, texting, nuance')
        ->and($blogPost->last_auto_saved_at)->not->toBeNull()
        ->and($blogPost->slangs)->toHaveCount(1);
});
